<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\School;
use App\Models\SmsLog;
use App\Models\Student;
use App\Models\StudentBill;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Automatic SMS to parents, switched on per school under SMS → Automatic SMS:
 * absence, results published, payment receipt and fee reminders.
 *
 * Every message carries a dedupe key, so one event texts a parent once even
 * if a register is saved twice or an exam is unpublished and published again.
 */
class ParentSms
{
    public const DEFAULTS = [
        'absence'       => false,
        'results'       => false,
        'receipt'       => false,
        'fee_reminder'  => false,
        'language'      => 'sw',
        'reminder_days' => 3,
        'signature'     => null,
    ];

    public const TEMPLATES = [
        'sw' => [
            'absence'      => 'Habari {guardian}, {student} ({class}) hakuhudhuria shule leo {date}. Tafadhali wasiliana na shule. {school}',
            'results'      => 'Mzazi, matokeo ya {exam} yametoka. Ingia kwenye programu ya ShulePRO kuona matokeo ya mwanafunzi wako. {school}',
            'receipt'      => 'Tumepokea TZS {amount} ya {bill} kwa {student}. Salio: TZS {balance}. Risiti #{receipt}. Asante. {school}',
            'fee_reminder' => 'Mzazi, kumbukumbu: {bill} ya {student}, salio TZS {balance}, inatakiwa kulipwa {due}. {school}',
            'fee_due'      => 'Mzazi, leo ni mwisho wa kulipa {bill} ya {student}. Salio: TZS {balance}. {school}',
        ],
        'en' => [
            'absence'      => 'Hello {guardian}, {student} ({class}) was absent from school today, {date}. Please contact the school. {school}',
            'results'      => 'Dear parent, {exam} results are out. Open the ShulePRO app to see your child\'s results. {school}',
            'receipt'      => 'Received TZS {amount} for {student}\'s {bill}. Balance: TZS {balance}. Receipt #{receipt}. Thank you. {school}',
            'fee_reminder' => 'Dear parent, reminder: {student}\'s {bill} balance of TZS {balance} is due on {due}. {school}',
            'fee_due'      => 'Dear parent, today is the last day to pay {student}\'s {bill}. Balance: TZS {balance}. {school}',
        ],
    ];

    public function __construct(private SmsService $sms) {}

    public static function settings(School $school): array
    {
        return array_merge(self::DEFAULTS, $school->sms_settings ?? []);
    }

    public static function render(string $template, array $vars): string
    {
        return trim(strtr($template, collect($vars)->mapWithKeys(fn ($v, $k) => ['{' . $k . '}' => (string) $v])->all()));
    }

    /** Sample messages for the settings page, using made-up names. */
    public static function previews(School $school, array $settings): array
    {
        $sig = self::signature($school, $settings);
        $t = self::TEMPLATES[$settings['language']] ?? self::TEMPLATES['sw'];
        $common = ['school' => $sig, 'guardian' => 'Amina', 'student' => 'Juma Hassan', 'class' => 'Form II A'];

        return [
            'absence'      => self::render($t['absence'], $common + ['date' => now()->format('d/m/Y')]),
            'results'      => self::render($t['results'], $common + ['exam' => 'Terminal Examination Term II']),
            'receipt'      => self::render($t['receipt'], $common + ['amount' => '380,000', 'bill' => 'School Fees - Term II', 'balance' => '0', 'receipt' => '00123']),
            'fee_reminder' => self::render($t['fee_reminder'], $common + ['bill' => 'School Fees - Term II', 'balance' => '150,000', 'due' => now()->addDays((int) $settings['reminder_days'])->format('d/m/Y')]),
            'fee_due'      => self::render($t['fee_due'], $common + ['bill' => 'School Fees - Term II', 'balance' => '150,000']),
        ];
    }

    private static function signature(School $school, array $settings): string
    {
        return trim($settings['signature'] ?: $school->name);
    }

    private function school(): ?School
    {
        return app()->bound('currentSchool') ? app('currentSchool') : null;
    }

    private function alreadySent(string $key): bool
    {
        return SmsLog::where('dedupe_key', $key)->where('status', 'sent')->exists();
    }

    private static function money($amount): string
    {
        return number_format((float) $amount, 0);
    }

    private static function guardianName($guardian): string
    {
        return trim($guardian->first_name ?? '') ?: 'Mzazi';
    }

    /**
     * @param  Collection<int, Student>  $students  students just marked absent
     */
    public function absences(Collection $students, string $date, string $className): int
    {
        $school = $this->school();
        if (! $school) return 0;
        $s = self::settings($school);
        if (! $s['absence']) return 0;

        $t = self::TEMPLATES[$s['language']] ?? self::TEMPLATES['sw'];
        $sent = 0;
        foreach ($students as $student) {
            $guardian = $student->loadMissing('guardian')->guardian;
            $key = "absence:{$student->id}:{$date}";
            if (! $guardian?->phone || $this->alreadySent($key)) continue;

            $msg = self::render($t['absence'], [
                'guardian' => self::guardianName($guardian),
                'student'  => trim("{$student->first_name} {$student->last_name}"),
                'class'    => $className,
                'date'     => Carbon::parse($date)->format('d/m/Y'),
                'school'   => self::signature($school, $s),
            ]);
            $r = $this->sms->sendSingle($guardian->phone, $msg, 'absence', trim("{$guardian->first_name} {$guardian->last_name}"), $key);
            $sent += $r['sent'];
        }
        return $sent;
    }

    /** One bulk message to the parents of every student who has marks in this exam. */
    public function resultsPublished(Exam $exam): int
    {
        $school = $this->school();
        if (! $school) return 0;
        $s = self::settings($school);
        $key = "results:{$exam->id}";
        if (! $s['results'] || $this->alreadySent($key)) return 0;

        $studentIds = Mark::where('exam_id', $exam->id)->distinct()->pluck('student_id');
        $recipients = Student::with('guardian')->whereIn('id', $studentIds)->get()
            ->pluck('guardian')->filter(fn ($g) => $g && $g->phone)
            ->unique('phone')
            ->map(fn ($g) => ['phone' => $g->phone, 'name' => trim("{$g->first_name} {$g->last_name}")])
            ->values()->all();
        if (! $recipients) return 0;

        $t = self::TEMPLATES[$s['language']] ?? self::TEMPLATES['sw'];
        $msg = self::render($t['results'], ['exam' => $exam->name, 'school' => self::signature($school, $s)]);

        return $this->sms->sendBulk($recipients, $msg, 'results', $key)['sent'];
    }

    public function paymentReceipt(Payment $payment): int
    {
        $school = $this->school();
        if (! $school) return 0;
        $s = self::settings($school);
        $key = "receipt:{$payment->id}";
        if (! $s['receipt'] || $this->alreadySent($key)) return 0;

        $payment->loadMissing('studentBill.bill', 'student.guardian');
        $guardian = $payment->student?->guardian;
        if (! $guardian?->phone) return 0;

        $t = self::TEMPLATES[$s['language']] ?? self::TEMPLATES['sw'];
        $msg = self::render($t['receipt'], [
            'amount'  => self::money($payment->amount),
            'bill'    => $payment->studentBill?->bill?->title ?? 'ada',
            'student' => trim("{$payment->student->first_name} {$payment->student->last_name}"),
            'balance' => self::money($payment->studentBill?->balance ?? 0),
            'receipt' => str_pad($payment->id, 5, '0', STR_PAD_LEFT),
            'school'  => self::signature($school, $s),
        ]);

        return $this->sms->sendSingle($guardian->phone, $msg, 'receipt', trim("{$guardian->first_name} {$guardian->last_name}"), $key)['sent'];
    }

    /**
     * Daily run (sms:fee-reminders): bills with a balance that fall due in
     * `reminder_days` days get a reminder; bills due today get a last-day notice.
     *
     * @return array{sent:int, checked:int}
     */
    public function feeReminders(School $school, ?Carbon $today = null): array
    {
        $s = self::settings($school);
        if (! $s['fee_reminder']) return ['sent' => 0, 'checked' => 0];

        $today = ($today ?? now())->copy()->startOfDay();
        $ahead = $today->copy()->addDays(max(1, (int) $s['reminder_days']));
        $t = self::TEMPLATES[$s['language']] ?? self::TEMPLATES['sw'];
        $sent = 0;

        $bills = StudentBill::with('bill', 'student.guardian')
            ->where('balance', '>', 0)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($q) use ($today, $ahead) {
                $q->whereDate('due_date', $today)->orWhereDate('due_date', $ahead);
            })
            ->get();

        foreach ($bills as $sb) {
            $guardian = $sb->student?->guardian;
            if (! $guardian?->phone) continue;

            $dueToday = $sb->due_date->isSameDay($today);
            $key = 'fee:' . $sb->id . ':' . $sb->due_date->toDateString() . ':' . ($dueToday ? 'due' : 'ahead');
            if ($this->alreadySent($key)) continue;

            $msg = self::render($t[$dueToday ? 'fee_due' : 'fee_reminder'], [
                'bill'    => $sb->bill?->title ?? 'ada',
                'student' => trim("{$sb->student->first_name} {$sb->student->last_name}"),
                'balance' => self::money($sb->balance),
                'due'     => $sb->due_date->format('d/m/Y'),
                'school'  => self::signature($school, $s),
            ]);
            $sent += $this->sms->sendSingle($guardian->phone, $msg, 'fee_reminder', trim("{$guardian->first_name} {$guardian->last_name}"), $key)['sent'];
        }

        return ['sent' => $sent, 'checked' => $bills->count()];
    }

    /**
     * Run a notification after the response has been sent, so a slow SMS
     * gateway never delays saving a register or recording a payment.
     */
    public static function afterResponse(callable $send): void
    {
        app()->terminating(function () use ($send) {
            try {
                $send(app(self::class));
            } catch (\Throwable $e) {
                Log::error('Automatic parent SMS failed: ' . $e->getMessage(), ['exception' => $e]);
            }
        });
    }
}
