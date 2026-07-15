<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\DailyReport;
use App\Models\Dormitory;
use App\Models\Department;
use App\Models\Event;
use App\Models\Leave;
use App\Models\Lending;
use App\Models\LessonPlan;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableSessionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $today = Carbon::today();
        $month = Carbon::now()->startOfMonth();

        // Guardians must never see the school-wide dashboard (finance, staff
        // counts, etc.) — send them to their own portal instead.
        if ($user->hasRole('guardian')) {
            return redirect()->route('guardian.dashboard');
        }

        $isManagement = $user->hasAnyRole(['Admin', 'Academic', 'HR']);

        // Finance Office members land on their finance dashboard (own tasks,
        // job description, role-matched quick actions), not the school-wide one.
        if (!$isManagement && $user->hasAnyRole([
            'treasurer', 'chief-accountant', 'accountant', 'class_accountant',
            'cashier', 'storekeeper', 'procurement_officer',
        ])) {
            return redirect()->route('treasurer.my-dashboard');
        }

        // Dorm Masters get the dormitory dashboard
        if (!$isManagement && $user->hasRole('Dorm Master') && !$user->hasAnyRole(['Teacher', 'HOD'])) {
            return redirect()->route('dormitories.dashboard');
        }

        // HODs with a department get their department analytics dashboard;
        // without one they fall through to the personal staff dashboard
        // (hod.dashboard would bounce them straight back here otherwise).
        if (!$isManagement && $user->hasRole('HOD')) {
            $hodStaff = Staff::where('user_id', $user->id)->first();
            if ($hodStaff?->department_id) {
                return redirect()->route('hod.dashboard');
            }
        }

        // Teachers and other staff get a personal performance dashboard
        if (!$isManagement && $user->hasAnyRole(['Teacher', 'HOD', 'Staff'])) {
            return $this->staffDashboard($user, $today, $month);
        }
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // ── Permission flags — each stat/widget below is only computed and
        // shown if the user actually holds the permission behind it, so the
        // dashboard reflects what this specific role can see/do rather than
        // showing school-wide data (finance, staff counts, etc.) to every
        // authenticated user regardless of role.
        $canViewStudents   = $user->can('view students');
        $canViewStaff      = $user->can('view staff');
        $canViewClasses    = $user->can('view classes') || $user->can('view subjects');
        $canViewPayments   = $user->can('view payments');
        $canViewTimetables = $user->can('view timetable');
        $canViewLeaves     = $user->can('view leaves');
        $canViewDorms      = $user->can('view dormitories') || $user->can('view departments');
        $canViewLibrary    = $user->can('view books');
        $canViewEvents     = $user->can('view events');

        // ── Core counts ─────────────────────────────────────────────────────
        $studentCount   = $canViewStudents ? Student::count() : 0;
        $staffCount     = $canViewStaff ? Staff::count() : 0;
        $classCount     = $canViewClasses ? SchoolClass::count() : 0;
        $subjectCount   = $canViewClasses ? Subject::count() : 0;
        $dormCount      = $canViewDorms ? Dormitory::count() : 0;
        $departmentCount = $canViewDorms ? Department::count() : 0;

        // ── Growth badges (this month vs last month) ─────────────────────────
        $studentGrowth = null;
        if ($canViewStudents) {
            $studentsThisMonth = Student::whereDate('created_at', '>=', $month)->count();
            $studentsLastMonth = Student::whereBetween('created_at', [$lastMonth, $month])->count();
            $studentGrowth     = $studentsLastMonth > 0
                ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100)
                : null;
        }

        // ── Academic session ─────────────────────────────────────────────────
        $currentSession = AcademicSession::where('is_current', true)->first();

        // ── Today's finance snapshot ─────────────────────────────────────────
        $todayCollection = $canViewPayments ? Payment::whereDate('payment_date', $today)->sum('amount') : 0;
        $monthCollection = $canViewPayments ? Payment::whereDate('payment_date', '>=', $month)->sum('amount') : 0;

        // ── Staff attendance today ───────────────────────────────────────────
        $staffPresentToday  = $canViewStaff ? Attendance::whereDate('date', $today)->where('status', 'present')->count() : 0;
        $staffAttendanceRate = $canViewStaff && $staffCount > 0 ? round(($staffPresentToday / $staffCount) * 100) : 0;

        // ── Pending leaves ───────────────────────────────────────────────────
        $pendingLeaves = $canViewLeaves ? Leave::where('status', 'pending')->count() : 0;

        // ── Published timetables ─────────────────────────────────────────────
        $publishedTimetables = $canViewTimetables ? Timetable::where('status', 'published')->count() : 0;

        // ── Events ───────────────────────────────────────────────────────────
        $eventStats     = collect();
        $upcomingEvents = collect();
        $calendarEvents = collect();
        if ($canViewEvents) {
            $eventStats    = Event::selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type');
            $upcomingEvents = Event::whereDate('start_date', '>=', $today)->orderBy('start_date')->take(5)->get();
            $calendarEvents = Event::whereDate('start_date', '>=', $today->copy()->startOfMonth())
                ->whereDate('end_date', '<=', $today->copy()->endOfMonth()->addMonths(2))
                ->get(['title', 'start_date', 'end_date', 'type'])
                ->map(fn($e) => [
                    'title' => $e->title,
                    'start' => $e->start_date,
                    'end'   => $e->end_date,
                    'color' => match($e->type) {
                        'academic' => '#007bff', 'sport' => '#28a745',
                        'cultural' => '#ffc107', 'holiday' => '#dc3545',
                        default    => '#6c757d',
                    },
                ]);
        }

        // ── Library stats (safe fallback) ────────────────────────────────────
        // Was DB::table('books')->count() — a raw query bypasses the tenant
        // scope entirely and showed the total across ALL schools. Also
        // referenced a 'book_lendings' table that doesn't exist (the real
        // table is 'lendings'), so borrowing stats were silently always 0.
        $libraryStats = ['books' => 0, 'active_borrowings' => 0, 'issued_this_month' => 0];
        if ($canViewLibrary) {
            try {
                $libraryStats = [
                    'books'             => Book::count(),
                    'active_borrowings' => Lending::whereNull('returned_at')->count(),
                    'issued_this_month' => Lending::whereDate('lend_date', '>=', $month)->count(),
                ];
            } catch (\Exception) {
                // keep the zeroed fallback
            }
        }

        // ── Recent students ──────────────────────────────────────────────────
        $recentStudents = $canViewStudents ? Student::latest()->take(5)->get() : collect();

        // ── Analytics charts (each behind its permission flag) ──────────────

        // Fee collection, last 6 months (one grouped query)
        $feeTrend = ['labels' => [], 'values' => []];
        if ($canViewPayments) {
            $rows = Payment::whereDate('payment_date', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount) as total")
                ->groupBy('ym')->pluck('total', 'ym');
            for ($i = 5; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $feeTrend['labels'][] = $m->format('M Y');
                $feeTrend['values'][] = (float) ($rows[$m->format('Y-m')] ?? 0);
            }
        }

        // Students per class + gender split
        $classDistribution = ['labels' => [], 'values' => []];
        $genderSplit = ['labels' => [], 'values' => []];
        if ($canViewStudents) {
            SchoolClass::withCount('students')->orderBy('name')->get()
                ->each(function ($c) use (&$classDistribution) {
                    $classDistribution['labels'][] = $c->name;
                    $classDistribution['values'][] = $c->students_count;
                });
            Student::selectRaw('gender, COUNT(*) as cnt')->groupBy('gender')->pluck('cnt', 'gender')
                ->each(function ($cnt, $gender) use (&$genderSplit) {
                    $genderSplit['labels'][] = ucfirst($gender ?: 'Unknown');
                    $genderSplit['values'][] = $cnt;
                });
        }

        // Latest published exam: average mark per class (one grouped query)
        $examPerformance = ['exam' => null, 'labels' => [], 'values' => []];
        if ($canViewClasses) {
            $latestExam = \App\Models\Exam::where('status', 'published')->latest('published_at')->first();
            if ($latestExam) {
                $examPerformance['exam'] = $latestExam->name;
                \App\Models\Mark::where('exam_id', $latestExam->id)
                    ->join('school_classes', 'school_classes.id', '=', 'marks.class_id')
                    ->selectRaw('school_classes.name as class_name, ROUND(AVG(marks.mark), 1) as avg_mark')
                    ->groupBy('school_classes.name')->orderBy('school_classes.name')
                    ->get()->each(function ($r) use (&$examPerformance) {
                        $examPerformance['labels'][] = $r->class_name;
                        $examPerformance['values'][] = (float) $r->avg_mark;
                    });
            }
        }

        // Teacher session attendance this week (coordinator-marked logs)
        $sessionWeek = ['attended' => 0, 'late' => 0, 'absent' => 0, 'unmarked' => 0];
        if ($canViewStaff) {
            TimetableSessionLog::whereDate('session_date', '>=', now()->startOfWeek(Carbon::MONDAY))
                ->selectRaw("COALESCE(status, 'unmarked') as s, COUNT(*) as cnt")
                ->groupBy('s')->pluck('cnt', 's')
                ->each(function ($cnt, $s) use (&$sessionWeek) {
                    if ($s === 'other') return;
                    $sessionWeek[$s] = $cnt;
                });
        }

        return view('home', compact(
            'canViewStudents', 'canViewStaff', 'canViewClasses', 'canViewPayments',
            'canViewTimetables', 'canViewLeaves', 'canViewDorms', 'canViewLibrary', 'canViewEvents',
            'studentCount', 'staffCount', 'classCount', 'subjectCount',
            'dormCount', 'departmentCount',
            'studentGrowth', 'currentSession',
            'todayCollection', 'monthCollection',
            'staffPresentToday', 'staffAttendanceRate', 'staffCount',
            'pendingLeaves', 'publishedTimetables',
            'eventStats', 'upcomingEvents', 'calendarEvents',
            'libraryStats',
            'recentStudents',
            'feeTrend', 'classDistribution', 'genderSplit', 'examPerformance', 'sessionWeek',
        ));
    }

    // ── Personal performance dashboard for Teacher / HOD ──────────────────
    private function staffDashboard($user, $today, $month)
    {
        $staff = Staff::where('user_id', $user->id)->with('department')->first();

        // Attendance this month (staff_id based)
        $attPresent = $attTotal = $attRate = 0;
        if ($staff) {
            $attPresent = Attendance::where('staff_id', $staff->id)
                ->where('status', 'present')->whereDate('date', '>=', $month)->count();
            $attTotal   = Attendance::where('staff_id', $staff->id)
                ->whereDate('date', '>=', $month)->count();
            $attRate    = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : 0;
        }

        // Leaves this year
        // The 'leaves' table has no 'days' column — it only stores
        // start_date/end_date, so the day count has to be derived.
        $leavesApproved = $leavesPending = 0;
        if ($staff) {
            $leavesApproved = Leave::where('staff_id', $staff->id)
                ->where('status', 'approved')->whereYear('start_date', now()->year)->get()
                ->sum(fn ($leave) => $leave->start_date->diffInDays($leave->end_date) + 1);
            $leavesPending  = Leave::where('staff_id', $staff->id)->where('status', 'pending')->count();
        }

        // Session logs (teacher_id = users.id)
        $todaySessions = TimetableSessionLog::with(['subject', 'schoolClass', 'period'])
            ->where('teacher_id', $user->id)
            ->whereDate('session_date', $today)
            ->orderBy('period_id')->get();

        $monthSessionCount = TimetableSessionLog::where('teacher_id', $user->id)
            ->whereDate('session_date', '>=', $month)->count();

        // Weekly sessions for chart (last 4 weeks, single grouped query)
        $weeklyData = TimetableSessionLog::where('teacher_id', $user->id)
            ->whereDate('session_date', '>=', now()->subWeeks(4)->startOfWeek())
            ->selectRaw('YEARWEEK(session_date, 1) as yw, COUNT(*) as cnt')
            ->groupByRaw('YEARWEEK(session_date, 1)')
            ->pluck('cnt', 'yw');
        $weeklyLabels = $weeklyCounts = [];
        for ($i = 3; $i >= 0; $i--) {
            $w = now()->subWeeks($i);
            $weeklyLabels[] = 'Wk ' . $w->startOfWeek()->format('d M');
            $weeklyCounts[] = $weeklyData[$w->format('oW')] ?? 0;
        }

        // Daily reports this month
        $monthReports   = DailyReport::where('teacher_id', $user->id)
            ->whereDate('report_date', '>=', $month)
            ->orderByDesc('report_date')->get(['id', 'report_date', 'status', 'submitted_at']);
        $todayReport    = $monthReports->first(fn($r) => $r->report_date->isToday());
        $submittedCount = $monthReports->where('status', 'submitted')->count();

        // Curriculum coverage from lesson plans
        $lessonPlans = LessonPlan::where('teacher_id', $user->id)
            ->with(['subject', 'schoolClass', 'topics.subtopics'])->get();
        $totalSubtopics = $coveredSubtopics = 0;
        foreach ($lessonPlans as $plan) {
            foreach ($plan->topics as $topic) {
                $totalSubtopics   += $topic->subtopics->count();
                $coveredSubtopics += $topic->subtopics->where('status', 'covered')->count();
            }
        }
        $coveragePct = $totalSubtopics > 0 ? round(($coveredSubtopics / $totalSubtopics) * 100) : 0;

        // Upcoming events
        $upcomingEvents = Event::whereDate('start_date', '>=', $today)->orderBy('start_date')->take(4)->get();

        return view('staff_dashboard', compact(
            'user', 'staff',
            'attPresent', 'attTotal', 'attRate',
            'leavesApproved', 'leavesPending',
            'todaySessions', 'monthSessionCount',
            'weeklyLabels', 'weeklyCounts',
            'monthReports', 'todayReport', 'submittedCount',
            'lessonPlans', 'totalSubtopics', 'coveredSubtopics', 'coveragePct',
            'upcomingEvents',
            'today', 'month',
        ));
    }
}
