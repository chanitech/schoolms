<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\SmsLog;
use App\Models\Staff;
use App\Services\ParentSms;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct(private SmsService $sms)
    {
        $this->middleware('permission:send sms');
    }

    public function compose()
    {
        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('sms.compose', compact('classes'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'audience'  => 'required|in:all_guardians,class_guardians,all_staff,custom',
            'class_id'  => 'required_if:audience,class_guardians|nullable|exists:school_classes,id',
            'custom_numbers' => 'required_if:audience,custom|nullable|string',
            'message'   => 'required|string|max:918', // 6 SMS segments (153 chars x 6)
        ]);

        $recipients = match ($data['audience']) {
            'all_guardians' => Guardian::whereNotNull('phone')->get(['first_name', 'last_name', 'phone'])
                ->map(fn($g) => ['name' => trim("{$g->first_name} {$g->last_name}"), 'phone' => $g->phone]),

            'class_guardians' => Guardian::whereHas('students', fn($q) => $q->where('class_id', $data['class_id']))
                ->whereNotNull('phone')->get(['first_name', 'last_name', 'phone'])
                ->map(fn($g) => ['name' => trim("{$g->first_name} {$g->last_name}"), 'phone' => $g->phone]),

            'all_staff' => Staff::whereNotNull('phone')->get(['first_name', 'last_name', 'phone'])
                ->map(fn($s) => ['name' => trim("{$s->first_name} {$s->last_name}"), 'phone' => $s->phone]),

            'custom' => collect(preg_split('/[\s,;\n]+/', $data['custom_numbers'], -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn($p) => ['name' => null, 'phone' => $p]),
        };

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'No recipients found for that audience.');
        }

        $result = $this->sms->sendBulk($recipients->all(), $data['message'], 'announcement');

        if ($result['error'] && $result['sent'] === 0) {
            return back()->withInput()->with('error', 'Sending failed: ' . $result['error']);
        }

        $summary = "{$result['sent']} sent";
        if ($result['invalid']) $summary .= ", {$result['invalid']} skipped (invalid number)";
        if ($result['failed']) $summary .= ", {$result['failed']} failed";

        return redirect()->route('sms.index')->with('success', "Message sent — {$summary}.");
    }

    /** Per-school switches for automatic parent SMS. */
    public function automatic()
    {
        abort_unless(app()->bound('currentSchool'), 404, 'Open a school first.');
        $school = app('currentSchool');
        $settings = ParentSms::settings($school);
        $previews = ParentSms::previews($school, $settings);
        $configured = $this->sms->isConfigured();
        $counts = SmsLog::whereIn('category', ['absence', 'results', 'receipt', 'fee_reminder'])
            ->where('created_at', '>=', now()->subDays(30))->where('status', 'sent')
            ->selectRaw('category, count(*) as n')->groupBy('category')->pluck('n', 'category');

        return view('sms.automatic', compact('school', 'settings', 'previews', 'configured', 'counts'));
    }

    public function saveAutomatic(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['Admin', 'Principal']) || $request->user()->is_super_admin, 403,
            'Only the head of school or an admin can change automatic SMS, since each message is paid for.');

        $data = $request->validate([
            'language'      => 'required|in:sw,en',
            'reminder_days' => 'required|integer|in:1,2,3,5,7,14',
            'signature'     => 'nullable|string|max:30',
        ]);

        abort_unless(app()->bound('currentSchool'), 404, 'Open a school first.');
        $school = app('currentSchool');
        $school->sms_settings = [
            'absence'       => $request->boolean('absence'),
            'results'       => $request->boolean('results'),
            'receipt'       => $request->boolean('receipt'),
            'fee_reminder'  => $request->boolean('fee_reminder'),
            'language'      => $data['language'],
            'reminder_days' => (int) $data['reminder_days'],
            'signature'     => $data['signature'] ?: null,
        ];
        $school->save();

        return redirect()->route('sms.automatic')->with('success', 'Automatic SMS settings saved.');
    }

    public function index(Request $request)
    {
        $query = SmsLog::with('sender')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);

        $logs = $query->paginate(30)->withQueryString();

        return view('sms.index', compact('logs'));
    }
}
