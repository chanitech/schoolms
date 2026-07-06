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
use Illuminate\Support\Facades\DB;

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

        // Staff-only users get a personal performance dashboard
        if ($user->hasAnyRole(['Teacher', 'HOD']) && !$user->hasAnyRole(['Admin', 'Academic', 'HR'])) {
            return $this->staffDashboard($user, $today, $month);
        }
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // ── Core counts ─────────────────────────────────────────────────────
        $studentCount   = Student::count();
        $staffCount     = Staff::count();
        $classCount     = SchoolClass::count();
        $subjectCount   = Subject::count();
        $dormCount      = Dormitory::count();
        $departmentCount = Department::count();

        // ── Growth badges (this month vs last month) ─────────────────────────
        $studentsThisMonth = Student::whereDate('created_at', '>=', $month)->count();
        $studentsLastMonth = Student::whereBetween('created_at', [$lastMonth, $month])->count();
        $studentGrowth     = $studentsLastMonth > 0
            ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100)
            : null;

        // ── Academic session ─────────────────────────────────────────────────
        $currentSession = AcademicSession::where('is_current', true)->first();

        // ── Today's finance snapshot ─────────────────────────────────────────
        $todayCollection = Payment::whereDate('payment_date', $today)->sum('amount');
        $monthCollection = Payment::whereDate('payment_date', '>=', $month)->sum('amount');

        // ── Staff attendance today ───────────────────────────────────────────
        $staffPresentToday  = Attendance::whereDate('date', $today)->where('status', 'present')->count();
        $staffAttendanceRate = $staffCount > 0 ? round(($staffPresentToday / $staffCount) * 100) : 0;

        // ── Pending leaves ───────────────────────────────────────────────────
        $pendingLeaves = Leave::where('status', 'pending')->count();

        // ── Published timetables ─────────────────────────────────────────────
        $publishedTimetables = Timetable::where('status', 'published')->count();

        // ── Events ───────────────────────────────────────────────────────────
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

        // ── Library stats (safe fallback) ────────────────────────────────────
        // Was DB::table('books')->count() — a raw query bypasses the tenant
        // scope entirely and showed the total across ALL schools. Also
        // referenced a 'book_lendings' table that doesn't exist (the real
        // table is 'lendings'), so borrowing stats were silently always 0.
        $libraryStats = [];
        try {
            $libraryStats = [
                'books'             => Book::count(),
                'active_borrowings' => Lending::whereNull('returned_at')->count(),
                'issued_this_month' => Lending::whereDate('lend_date', '>=', $month)->count(),
            ];
        } catch (\Exception) {
            $libraryStats = ['books' => 0, 'active_borrowings' => 0, 'issued_this_month' => 0];
        }

        // ── Recent students ──────────────────────────────────────────────────
        $recentStudents = Student::latest()->take(5)->get();

        return view('home', compact(
            'studentCount', 'staffCount', 'classCount', 'subjectCount',
            'dormCount', 'departmentCount',
            'studentGrowth', 'studentsThisMonth',
            'currentSession',
            'todayCollection', 'monthCollection',
            'staffPresentToday', 'staffAttendanceRate', 'staffCount',
            'pendingLeaves', 'publishedTimetables',
            'eventStats', 'upcomingEvents', 'calendarEvents',
            'libraryStats',
            'recentStudents',
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
        $leavesApproved = $leavesPending = 0;
        if ($staff) {
            $leavesApproved = Leave::where('staff_id', $staff->id)
                ->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days');
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
