<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Gives the AI assistant read access to every school table, but only the
 * tables the signed-in user can already open in the menu. The abilities
 * below mirror the `can` gates in config/adminlte.php, so the assistant
 * never shows a teacher what the teacher's own screens would not.
 */
class AIDataAccess
{
    /** model => [what it holds, abilities (any one is enough; null = every staff member)] */
    public const SOURCES = [
        // Students & guardians
        'Student'            => ['Student records: names, admission no, gender, date of birth, class, guardian, dormitory, status, phone', ['view students', 'view results', 'is-class-coordinator']],
        'Guardian'           => ['Parents/guardians: names, relation, phone, email, address, occupation', ['view guardians']],
        'Enrollment'         => ['Which class each student is enrolled in per academic session', ['view enrollments', 'view students']],
        'StudentAttendance'  => ['Daily student attendance (present/absent/late/excused) per date and class', ['is-class-coordinator', 'view students']],
        // Academic
        'AcademicSession'    => ['Academic years/sessions and which one is current', null],
        'SchoolClass'        => ['Classes/streams and their class teachers', null],
        'Subject'            => ['Subjects offered', null],
        'Division'           => ['Division bands used for results', null],
        'Grade'              => ['Grading scale (grade letters, mark ranges, points)', null],
        'Exam'               => ['Exams: name, term, session, status (draft/reviewed/published)', null],
        'Mark'               => ['Student marks per subject per exam (column `mark`)', ['view marks', 'view results', 'enter marks']],
        'MarkQuestionScore'  => ['Per-question marks for exams marked question by question', ['enter marks', 'view marks']],
        'StudentResult'      => ['Computed results per exam: GPA, total points, division', ['view results', 'view marks']],
        'ExamQuestion'       => ['Exam question papers', ['manage-exam-questions', 'enter marks']],
        'Timetable'          => ['School timetables', ['view timetable']],
        'TimetableEntry'     => ['Timetable periods: subject, class, teacher, day, time', ['view timetable']],
        'TimetablePeriod'    => ['Period/bell times', ['view timetable']],
        'TimetableSessionLog'=> ['Whether each timetabled lesson was attended by the teacher', ['view timetable']],
        'DailyReport'        => ['Teachers\' daily teaching reports', ['view daily reports']],
        'DailyReportActivity'=> ['Activities inside daily teaching reports', ['view daily reports']],
        'LessonPlan'         => ['Lesson plans', ['view lesson plans']],
        'LessonTopic'        => ['Syllabus topics and coverage', ['view lesson plans']],
        'LessonSubtopic'     => ['Syllabus subtopics and coverage', ['view lesson plans']],
        // Boarding
        'Dormitory'              => ['Dormitories/hostels', ['view dormitories']],
        'DormitoryRoom'          => ['Dormitory rooms', ['view dormitories']],
        'DormitoryBed'           => ['Dormitory beds', ['view dormitories']],
        'DormitoryBedAllocation' => ['Which student sleeps in which bed', ['view dormitories']],
        // Counseling office
        'CounselingIntakeForm'         => ['Counseling intake forms: reason for counseling, chief complaint, health problems, family background', ['view counseling intake forms']],
        'IndividualSessionReport'      => ['Individual counseling session notes: presenting problem, work done, progress, intervention plan, follow-up', ['view session reports']],
        'GroupCounselingSessionReport' => ['Group counseling session notes', ['view group sessions']],
        'ClassroomGuidance'            => ['Classroom guidance sessions per class', ['view classroom guidance']],
        'InterestInventory'            => ['Career interest inventory answers per student', ['view interest inventories']],
        'AptitudeAttempt'              => ['Aptitude/psychometric test attempts and scores per student', ['view aptitude tests']],
        'AptitudeAnswer'               => ['Answers given in aptitude tests', ['view aptitude tests']],
        'AptitudeQuestion'             => ['Aptitude test questions', ['view aptitude tests', 'create aptitude questions']],
        // Staff & HR
        'Staff'              => ['Staff: names, position, department, phone, email, hire date', ['view staff']],
        'Department'         => ['Departments', null],
        'Attendance'         => ['Staff attendance (biometric/clock-in)', ['view attendance']],
        'Leave'              => ['Staff leave requests and status', ['view leaves']],
        'JobCard'            => ['Staff job cards/tasks', ['view any jobcards']],
        'Event'              => ['School events and calendar', ['view events']],
        'StaffSalaryHistory' => ['Staff salary changes', ['is-treasurer', 'is-loan-approver', 'role:Admin|Principal']],
        // Finance
        'Bill'               => ['Fee bills per class (title, amount, due date)', ['view bills']],
        'StudentBill'        => ['Each student\'s bill: total, paid, balance, status, due date', ['view student bills', 'view payments']],
        'Payment'            => ['Fee payments received (amount, method, reference, date)', ['view payments']],
        'PocketTransaction'  => ['Student pocket money deposits and withdrawals', ['view pocket money']],
        'Loan'               => ['Staff loans', ['is-loan-approver']],
        'LoanRepayment'      => ['Staff loan repayments', ['is-loan-approver']],
        'LoanCategory'       => ['Loan categories', ['is-loan-approver', 'is-treasurer']],
        'BankStatement'      => ['Staff bank statements uploaded for loans', ['is-loan-approver']],
        'Budget'             => ['Department budgets', ['view budgets', 'view pending approvals']],
        'BudgetItem'         => ['Budget line items', ['view budgets', 'view pending approvals']],
        'Invoice'            => ['Supplier invoices', ['view invoices', 'approve invoices', 'pay invoices']],
        'ExpenseLog'         => ['School expenses', ['is-finance-office']],
        'ProcurementRequest' => ['Procurement requests and approvals', ['approve procurement requests', 'headmaster approve procurement requests', 'disburse payments', 'create procurement requests']],
        'StockRequest'       => ['Requests for items from the store', ['review stock requests', 'create stock requests']],
        'InventoryItem'      => ['Store/inventory items and stock levels', ['view inventory', 'manage stock', 'manage settings']],
        'InventoryCategory'  => ['Inventory categories', ['view inventory', 'manage stock', 'manage settings']],
        'InventoryTransaction' => ['Stock movements in/out of the store', ['view inventory', 'manage stock', 'manage settings']],
        'TaskLog'            => ['Finance office task logs', ['is-finance-office']],
        // Transport, library, communication
        'Bus'                        => ['School buses', ['view transport']],
        'BusRoute'                   => ['Bus routes', ['view transport']],
        'BusStop'                    => ['Bus stops', ['view transport']],
        'StudentTransportAssignment' => ['Which student uses which route/stop', ['view transport']],
        'TransportFee'               => ['Transport fees', ['view transport']],
        'TransportPayment'           => ['Transport fee payments', ['view transport']],
        'Book'               => ['Library books and quantities', ['view books']],
        'Category'           => ['Library book categories', ['view categories', 'view books']],
        'Lending'            => ['Library lending: who borrowed which book, returned or not', ['view lendings']],
        'Notice'             => ['Notice board announcements', null],
        'Suggestion'         => ['Suggestions submitted by staff/parents', ['manage suggestions']],
        'Document'           => ['Document library files', ['view documents']],
        'SmsLog'             => ['SMS sent to parents/staff and delivery status', ['send sms']],
    ];

    /** Never shown to the AI, whoever asks. */
    private const HIDDEN = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'api_key', 'biometric_api_key', 'secret', 'token', 'signature', 'school_id', 'deleted_at'];

    /** Shown only to finance roles and the head of school. */
    private const PAY_COLUMNS = ['basic_salary', 'salary', 'old_salary', 'new_salary'];

    private const OPS = ['=', '!=', '>', '>=', '<', '<=', 'like', 'in', 'null', 'not_null', 'between'];

    public function can(?array $abilities): bool
    {
        $user = Auth::user();
        if (! $user) return false;
        if ($abilities === null || $user->is_super_admin) return true;

        foreach ($abilities as $a) {
            if (str_starts_with($a, 'role:')) {
                if ($user->hasAnyRole(explode('|', substr($a, 5)))) return true;
            } elseif ($user->can($a)) {
                return true;
            }
        }
        return false;
    }

    public function canModel(string $model): bool
    {
        return isset(self::SOURCES[$model]) && $this->can(self::SOURCES[$model][1]);
    }

    private function canSeePay(): bool
    {
        return $this->can(['is-treasurer', 'is-loan-approver', 'is-finance-office', 'role:Admin|Principal']);
    }

    private function columns(string $model): array
    {
        $class = 'App\\Models\\' . $model;
        $cols = Schema::getColumnListing((new $class)->getTable());
        $hidden = self::HIDDEN;
        if (! $this->canSeePay()) $hidden = array_merge($hidden, self::PAY_COLUMNS);

        return array_values(array_diff($cols, $hidden));
    }

    /** What the AI may query for this user, and what exists but is restricted. */
    public function sources(): array
    {
        $allowed = $restricted = [];
        foreach (self::SOURCES as $model => [$desc, $abilities]) {
            if (! class_exists('App\\Models\\' . $model)) continue;
            if ($this->can($abilities)) {
                $allowed[] = "• {$model}: {$desc}. Columns: " . implode(', ', $this->columns($model));
            } else {
                $restricted[] = "• {$model}: {$desc}";
            }
        }

        $summary = "DATA YOU CAN QUERY with query_data (model name, then columns):\n" . implode("\n", $allowed);
        if ($restricted) {
            $summary .= "\n\nEXISTS BUT THIS USER'S ROLE HAS NO ACCESS (say so if asked; never claim it does not exist):\n" . implode("\n", $restricted);
        }

        return ['_exact_summary' => $summary, '_internal' => true];
    }

    /**
     * Generic read query: filters [{column, op, value}] (or {column: value}),
     * optional columns, order_by/order_dir, limit (max 100), count_only, group_by.
     */
    public function query(array $args): array
    {
        $model = Str::studly(trim($args['model'] ?? ''));
        if (! isset(self::SOURCES[$model]) || ! class_exists('App\\Models\\' . $model)) {
            return ['_exact_summary' => "There is no data area called '{$model}'. Call list_data_sources to see what exists."];
        }
        if (! $this->canModel($model)) {
            return ['_exact_summary' => "Your role does not have access to " . self::SOURCES[$model][0] . ". Ask the head of school or an admin if you need it."];
        }

        $class = 'App\\Models\\' . $model;
        $allowedCols = $this->columns($model);
        $query = $class::query();

        // filters: list of {column, op, value} or a plain {column: value} map
        $filters = $args['filters'] ?? [];
        if (is_array($filters) && $filters && ! array_is_list($filters)) {
            $filters = collect($filters)->map(fn ($v, $k) => ['column' => $k, 'op' => '=', 'value' => $v])->values()->all();
        }
        foreach ((array) $filters as $f) {
            $col = $f['column'] ?? null; $op = strtolower($f['op'] ?? '='); $val = $f['value'] ?? null;
            if (! in_array($col, $allowedCols, true)) return ['_exact_summary' => "Unknown column '{$col}' for {$model}. Columns: " . implode(', ', $allowedCols)];
            if (! in_array($op, self::OPS, true)) return ['_exact_summary' => "Unsupported filter '{$op}'. Use one of: " . implode(', ', self::OPS)];
            if (! empty($f['value_column'])) {
                // compare two columns, e.g. quantity_in_stock < minimum_stock
                if (! in_array($f['value_column'], $allowedCols, true) || ! in_array($op, ['=', '!=', '>', '>=', '<', '<='], true)) {
                    return ['_exact_summary' => "Can't compare {$col} with '{$f['value_column']}'."];
                }
                $query->whereColumn($col, $op === '!=' ? '<>' : $op, $f['value_column']);
                continue;
            }
            match ($op) {
                'like'     => $query->where($col, 'like', str_contains((string) $val, '%') ? $val : "%{$val}%"),
                'in'       => $query->whereIn($col, (array) $val),
                'null'     => $query->whereNull($col),
                'not_null' => $query->whereNotNull($col),
                'between'  => $query->whereBetween($col, array_slice((array) $val, 0, 2)),
                default    => $query->where($col, $op, $val),
            };
        }

        if (! empty($args['group_by'])) {
            $g = $args['group_by'];
            if (! in_array($g, $allowedCols, true)) return ['_exact_summary' => "Unknown column '{$g}' for {$model}."];
            $rows = $query->selectRaw("`{$g}` as grp, count(*) as total")->groupBy($g)->orderByDesc('total')->limit(50)->get();
            $names = $this->names([$g => $rows->pluck('grp')->all()]);
            $lines = $rows->map(fn ($r) => '• ' . ($names[$g][$r->grp] ?? ($r->grp ?? 'none')) . ": {$r->total}");
            return ['_exact_summary' => "{$model} grouped by {$g}:\n" . $lines->implode("\n")];
        }

        if (! empty($args['count_only'])) {
            return ['_exact_summary' => "{$model}: " . $query->count() . ' record(s) match.'];
        }

        $cols = array_values(array_intersect((array) ($args['columns'] ?? []), $allowedCols)) ?: $allowedCols;
        if (! in_array('id', $cols, true) && in_array('id', $allowedCols, true)) array_unshift($cols, 'id');
        $query->select($cols);

        if (! empty($args['order_by']) && in_array($args['order_by'], $allowedCols, true)) {
            $query->orderBy($args['order_by'], strtolower($args['order_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc');
        } else {
            $query->latest('id');
        }

        $limit = max(1, min(100, (int) ($args['limit'] ?? 20)));
        $total = (clone $query)->toBase()->getCountForPagination();
        $rows = $query->limit($limit)->get();
        if ($rows->isEmpty()) return ['_exact_summary' => "No {$model} records match."];

        $refs = [];
        foreach (array_keys(self::REFS) as $fk) {
            if (in_array($fk, $cols, true)) $refs[$fk] = $rows->pluck($fk)->filter()->unique()->all();
        }
        $names = $this->names($refs);

        $out = "{$model}: showing " . $rows->count() . " of {$total} record(s)\n";
        foreach ($rows as $r) {
            $parts = [];
            foreach ($r->getAttributes() as $k => $v) {
                if ($v === null || $v === '') continue;
                $v = is_scalar($v) ? (string) $v : json_encode($v);
                $parts[] = "{$k}: " . Str::limit(preg_replace('/\s+/', ' ', $v), 300);
                if (isset($names[$k][$r->$k])) $parts[] = str_replace('_id', '', $k) . '_name: ' . $names[$k][$r->$k];
            }
            $out .= '• ' . implode(' | ', $parts) . "\n";
        }

        return ['_exact_summary' => rtrim($out)];
    }

    /**
     * Everything about one student on one page, section by section, each
     * section only if this user's role may see it.
     */
    public function studentProfile(string $query): array
    {
        $query = trim($query);
        $s = Student::with(['schoolClass', 'dormitory', 'guardian'])
            ->where(fn ($q) => $q->where('admission_no', $query)->orWhere('id', is_numeric($query) ? (int) $query : 0)
                ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$query}%"])
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]))
            ->first();
        if (! $s) return ['_exact_summary' => "No student found for '{$query}'."];
        if (! $this->canModel('Student')) return ['_exact_summary' => 'Your role does not have access to student records.'];

        $name = trim("{$s->first_name} {$s->middle_name} {$s->last_name}");
        $L = ["👤 **{$name}** (Adm: {$s->admission_no}, ID: {$s->id})",
              "Class: " . ($s->schoolClass->name ?? 'Unassigned') . " · Gender: {$s->gender} · Status: {$s->status}"
              . ($s->date_of_birth ? " · Born: {$s->date_of_birth}" : '')];
        $denied = [];

        if ($this->canModel('Guardian') && $s->guardian) {
            $g = $s->guardian;
            $L[] = "Guardian: {$g->first_name} {$g->last_name}" . ($g->relation_to_student ? " ({$g->relation_to_student})" : '') . " · Phone: {$g->phone}";
        }

        $session = AcademicSession::where('is_current', true)->first();
        if ($session && $this->canModel('StudentAttendance')) {
            $a = \App\Models\StudentAttendance::summaries(collect([$s->id]), $session->id, now())->get($s->id);
            $L[] = $a ? "Attendance ({$session->name}): {$a['rate']}% — present {$a['attended']} of {$a['days']} days, absent {$a['absent']}, late {$a['late']}" : 'Attendance: no register entries this session';
        }

        if ($this->canModel('StudentResult')) {
            $res = \App\Models\StudentResult::with('exam')->where('student_id', $s->id)->latest('id')->limit(4)->get();
            $L[] = $res->isNotEmpty()
                ? 'Results: ' . $res->map(fn ($r) => ($r->exam->name ?? 'Exam') . " — GPA {$r->gpa}, points {$r->total_points}, Div {$r->division}")->implode('; ')
                : 'Results: none yet';
        } else $denied[] = 'results';

        if ($this->canModel('StudentBill')) {
            $bills = \App\Models\StudentBill::where('student_id', $s->id)->get();
            $L[] = 'Fees: billed TZS ' . number_format($bills->sum('total_amount')) . ', paid TZS ' . number_format($bills->sum('amount_paid')) . ', balance TZS ' . number_format($bills->sum('balance'));
        } else $denied[] = 'fees';

        if ($this->canModel('PocketTransaction')) {
            $bal = \App\Models\PocketTransaction::where('student_id', $s->id)->selectRaw("SUM(CASE WHEN type='deposit' THEN amount ELSE -amount END) b")->value('b');
            $L[] = 'Pocket money balance: TZS ' . number_format((float) $bal);
        }

        if ($this->canModel('Dormitory')) $L[] = 'Dormitory: ' . ($s->dormitory->name ?? 'Day scholar');

        if ($this->canModel('StudentTransportAssignment')) {
            $t = \App\Models\StudentTransportAssignment::where('student_id', $s->id)->where('status', 'active')->first();
            if ($t) $L[] = "Transport: route #{$t->route_id}, stop #{$t->stop_id}";
        }

        // Counseling office — the most sensitive part, counselors and the head only
        $c = [];
        if ($this->canModel('CounselingIntakeForm')) {
            foreach (\App\Models\CounselingIntakeForm::where('student_id', $s->id)->latest('id')->limit(3)->get() as $f) {
                $c[] = "Intake " . $f->created_at?->format('d/m/Y') . ": reason — " . Str::limit($f->reason_for_counseling ?: '-', 200)
                    . "; chief complaint — " . Str::limit($f->chief_complaint ?: '-', 200)
                    . ($f->health_problems ? '; health — ' . Str::limit($f->health_problems, 120) : '');
            }
        }
        if ($this->canModel('IndividualSessionReport')) {
            foreach (\App\Models\IndividualSessionReport::where('student_id', $s->id)->latest('date')->limit(5)->get() as $r) {
                $c[] = "Session {$r->session_number} ({$r->date}): problem — " . Str::limit($r->presenting_problem ?: '-', 160)
                    . "; progress — " . Str::limit($r->assessment_progress ?: '-', 160) . "; plan — " . Str::limit($r->intervention_plan ?: '-', 160);
            }
        }
        if ($this->canModel('AptitudeAttempt')) {
            foreach (\App\Models\AptitudeAttempt::where('student_id', $s->id)->latest('id')->limit(3)->get() as $t) {
                $c[] = 'Aptitude test ' . $t->created_at?->format('d/m/Y') . ": score {$t->total_score}";
            }
        }
        if ($this->canModel('InterestInventory')) {
            $n = \App\Models\InterestInventory::where('student_id', $s->id)->count();
            if ($n) $c[] = "Interest inventories completed: {$n}";
        }
        if ($this->canModel('IndividualSessionReport') || $this->canModel('CounselingIntakeForm')) {
            $L[] = $c ? "🧠 Counseling:\n  - " . implode("\n  - ", $c) : '🧠 Counseling: no records';
        } else $denied[] = 'counseling records';

        if ($denied) $L[] = '(Not shown — your role has no access to: ' . implode(', ', $denied) . ')';

        return ['_exact_summary' => implode("\n", $L)];
    }

    private const REFS = [
        'student_id' => 'student', 'class_id' => 'class', 'school_class_id' => 'class', 'subject_id' => 'subject',
        'exam_id' => 'exam', 'staff_id' => 'staff', 'teacher_id' => 'staff', 'class_teacher_id' => 'staff',
        'guardian_id' => 'guardian', 'academic_session_id' => 'session',
    ];

    /** Turn foreign keys into names so answers say "Peter Mbwana", not "student_id 1001535". */
    private function names(array $refs): array
    {
        $out = [];
        foreach ($refs as $fk => $ids) {
            $ids = array_values(array_filter((array) $ids));
            if (! $ids || ! isset(self::REFS[$fk])) continue;
            $out[$fk] = match (self::REFS[$fk]) {
                'student'  => Student::whereIn('id', $ids)->get()->mapWithKeys(fn ($s) => [$s->id => trim("{$s->first_name} {$s->last_name}")])->all(),
                'class'    => SchoolClass::whereIn('id', $ids)->pluck('name', 'id')->all(),
                'subject'  => Subject::whereIn('id', $ids)->pluck('name', 'id')->all(),
                'exam'     => Exam::whereIn('id', $ids)->pluck('name', 'id')->all(),
                'staff'    => Staff::whereIn('id', $ids)->get()->mapWithKeys(fn ($s) => [$s->id => trim("{$s->first_name} {$s->last_name}")])->all(),
                'guardian' => Guardian::whereIn('id', $ids)->get()->mapWithKeys(fn ($g) => [$g->id => trim("{$g->first_name} {$g->last_name}")])->all(),
                'session'  => AcademicSession::whereIn('id', $ids)->pluck('name', 'id')->all(),
            };
        }
        return $out;
    }
}
