<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\JobCard;
use App\Models\Event;
use App\Models\TimetableSessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeavesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class HRReportController extends Controller
{
    public function index()
    {
        return view('hr-reports.index');
    }

    // -----------------------------
    // Attendance Report
    // -----------------------------
    public function attendanceReport()
    {
        $attendanceSummary = Attendance::selectRaw('
                staff_id,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
                (SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as attendance_percent
            ')
            ->groupBy('staff_id')
            ->with('staff.department')
            ->get();

        $attendanceRate = $attendanceSummary
            ->groupBy(fn($row) => $row->staff->department->name ?? 'N/A')
            ->map(fn($rows, $dept) => [
                'department_name' => $dept,
                'attendance_percent' => $rows->avg('attendance_percent'),
            ])
            ->values();

        $absentRate = $attendanceSummary
            ->groupBy(fn($row) => $row->staff->department->name ?? 'N/A')
            ->map(fn($rows, $dept) => [
                'department_name' => $dept,
                'absent_percent' => 100 - $rows->avg('attendance_percent'),
            ])
            ->values();

        return view('hr-reports.attendance', compact('attendanceSummary', 'attendanceRate', 'absentRate'));
    }

    // -----------------------------
    // Leave Report
    // -----------------------------
    
    

    public function leaveReport(Request $request)
{
    $departments = Department::all();

    $query = Leave::query()->with(['staff.department']);

    // Dynamic filters (optional, currently you have none in the blade)
    $staffs = collect();
    if ($request->filled('department_id')) {
        $query->whereHas('staff', fn($q) => $q->where('department_id', $request->department_id));
        $staffs = Staff::where('department_id', $request->department_id)->get();
    }

    if ($request->filled('staff_id')) {
        $query->where('staff_id', $request->staff_id);
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('date_from')) {
        $query->whereDate('start_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('end_date', '<=', $request->date_to);
    }

    $leaves = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();

    // --- Summary for Charts ---
    $leaveSummaryByType = Leave::select('type', DB::raw('count(*) as total'))
        ->groupBy('type')
        ->pluck('total', 'type'); // key = type, value = total

    $leaveSummaryByStatus = Leave::select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status'); // key = status, value = total

    // Total counts
    $summary = [
        'pending'  => Leave::where('status', 'pending')->count(),
        'approved' => Leave::where('status', 'approved')->count(),
        'rejected' => Leave::where('status', 'rejected')->count(),
    ];

    return view('hr-reports.leave', compact(
        'leaves', 'departments', 'staffs', 'summary',
        'leaveSummaryByType', 'leaveSummaryByStatus'
    ));
}





    public function exportLeaves(Request $request)
    {
        return Excel::download(new LeavesExport($request->all()), 'leaves.xlsx');
    }

    public function exportLeaveExcel(Request $request)
    {
        return Excel::download(new LeavesExport($request->all()), 'leaves.xlsx');
    }

    public function exportLeavePDF(Request $request)
    {
        $query = Leave::with(['staff.department']);

        if ($request->filled('staff_name')) {
            $query->whereHas('staff', fn($q) => $q->where('name', 'like', '%'.$request->staff_name.'%'));
        }
        if ($request->filled('department_id')) {
            $query->whereHas('staff', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $leaves = $query->get();
        $pdf = Pdf::loadView('hr-reports.leave-pdf', compact('leaves'));
        return $pdf->download('leaves.pdf');
    }

    // -----------------------------
    // Job Cards Report
    // -----------------------------
    
     public function jobCardReport(Request $request)
{
    // Fetch all staff for filter dropdown
    $staff = Staff::all();

    // Base query
    $query = JobCard::with(['assignee', 'assigner']);

    // Apply filters if any
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('assignee')) {
        $query->where('assigned_to', $request->assignee);
    }

    // Paginate results for table
    $jobCards = $query->orderBy('due_date', 'desc')->paginate(15);

    // Summary for charts
    $jobCardSummaryByStatus = JobCard::select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

    $jobCardSummaryByStaff = JobCard::select('assigned_to', DB::raw('count(*) as total'))
        ->groupBy('assigned_to')
        ->with('assignee')
        ->get()
        ->mapWithKeys(function ($item) {
            $name = $item->assignee->name ?? 'N/A';
            return [$name => $item->total];
        });

    return view('hr-reports.jobcards', compact(
        'jobCards', 
        'jobCardSummaryByStatus', 
        'jobCardSummaryByStaff', 
        'staff'
    ));}


    // -----------------------------
    // Staff Report
    // -----------------------------
    public function staffReport()
    {
        $staffByDept = Staff::select('department_id', DB::raw('count(*) as total'))
            ->groupBy('department_id')
            ->with('department')
            ->get();

        $rolesCount = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        return view('hr-reports.staff', compact('staffByDept', 'rolesCount'));
    }

    // -----------------------------
    // Events Report
    // -----------------------------
    public function eventReport()
    {
        $events = Event::select('title', 'date', 'participants_count')
            ->orderBy('date', 'desc')
            ->get();

        return view('hr-reports.events', compact('events'));
    }

    // -----------------------------
    // Summary Dashboard
    // -----------------------------
    public function summaryDashboard()
    {
        $totalStaff = Staff::count();
        $totalDepartments = Department::count();
        $totalLeaves = Leave::count();
        $totalJobCards = JobCard::count();
        $totalEvents = Event::count();

        $leavesByType = Leave::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        $attendanceStats = Attendance::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return view('hr-reports.summary', compact(
            'totalStaff', 'totalDepartments', 'totalLeaves',
            'totalJobCards', 'totalEvents', 'leavesByType', 'attendanceStats'
        ));
    }







public function evaluationReport()
{
    $staffList = Staff::with('department')->get();

    // 1️⃣ Aggregate Attendance per staff
    $attendanceData = Attendance::selectRaw('staff_id, 
        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
        COUNT(*) as total_days')
        ->groupBy('staff_id')
        ->pluck('present_days', 'staff_id'); // we will divide by total_days next

    $attendanceTotal = Attendance::selectRaw('staff_id, COUNT(*) as total_days')
        ->groupBy('staff_id')
        ->pluck('total_days', 'staff_id');

    // 3️⃣ Aggregate JobCard completion per staff
    $jobCardData = JobCard::selectRaw('assigned_to, 
        SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) as completed, 
        COUNT(*) as total')
        ->groupBy('assigned_to')
        ->pluck('completed', 'assigned_to');

    $jobCardTotal = JobCard::selectRaw('assigned_to, COUNT(*) as total')
        ->groupBy('assigned_to')
        ->pluck('total', 'assigned_to');

    // 4️⃣ Lesson plan completion per teacher (by user_id on staff)
    $lessonData = DB::table('lesson_plans')
        ->join('lesson_topics', 'lesson_topics.lesson_plan_id', '=', 'lesson_plans.id')
        ->join('lesson_subtopics', 'lesson_subtopics.lesson_topic_id', '=', 'lesson_topics.id')
        ->whereNull('lesson_plans.deleted_at')
        ->selectRaw('lesson_plans.teacher_id,
            COUNT(lesson_subtopics.id) as total_subtopics,
            SUM(CASE WHEN lesson_subtopics.status = "covered" THEN 1 ELSE 0 END) as covered_subtopics')
        ->groupBy('lesson_plans.teacher_id')
        ->get()
        ->keyBy('teacher_id');  // keyed by users.id

    // 5️⃣ Session attendance per teacher (timetable_session_logs)
    // "present" = attended or late; absent/other count against attendance rate
    $sessionData = TimetableSessionLog::selectRaw(
            'teacher_id,
             COUNT(*) as total_sessions,
             SUM(CASE WHEN status IN ("attended","late") THEN 1 ELSE 0 END) as sessions_taught'
        )
        ->groupBy('teacher_id')
        ->get()
        ->keyBy('teacher_id');  // keyed by users.id

    // 6️⃣ Map staff to evaluations
    $evaluations = $staffList->map(function($staff) use ($attendanceData, $attendanceTotal, $jobCardData, $jobCardTotal, $lessonData, $sessionData) {

    $present = $attendanceData[$staff->id] ?? 0;
    $totalAttendance = $attendanceTotal[$staff->id] ?? 0;
    $attendanceRate = $totalAttendance > 0 ? ($present / $totalAttendance) * 100 : 0;

    $completed = $jobCardData[$staff->id] ?? 0;
    $totalJobCards = $jobCardTotal[$staff->id] ?? 0;
    $jobCardRate = $totalJobCards > 0 ? ($completed / $totalJobCards) * 100 : 0;

    // Lesson completion (teacher_id on lesson_plans = users.id = staff.user_id)
    $lessonRow        = $lessonData[$staff->user_id] ?? null;
    $totalSubtopics   = $lessonRow?->total_subtopics ?? 0;
    $coveredSubtopics = $lessonRow?->covered_subtopics ?? 0;
    $lessonRate       = $totalSubtopics > 0 ? ($coveredSubtopics / $totalSubtopics) * 100 : null;

    // Session attendance (timetable sessions taught rate)
    $sessionRow      = $sessionData[$staff->user_id] ?? null;
    $totalSessions   = $sessionRow?->total_sessions ?? 0;
    $sessionsTaught  = $sessionRow?->sessions_taught ?? 0;
    $sessionRate     = $totalSessions > 0 ? ($sessionsTaught / $totalSessions) * 100 : null;

    // Weights: 30% attendance + 20% job card + 20% lesson plan + 30% session attendance
    // Fall back gracefully when data is missing
    $weights = [];
    $score   = 0;

    $weights[] = ['rate' => $attendanceRate, 'w' => 30];
    $weights[] = ['rate' => $jobCardRate,    'w' => 20];
    if ($lessonRate !== null)  $weights[] = ['rate' => $lessonRate,  'w' => 20];
    if ($sessionRate !== null) $weights[] = ['rate' => $sessionRate, 'w' => 30];

    $totalWeight = array_sum(array_column($weights, 'w'));
    foreach ($weights as $wt) {
        $score += ($wt['rate'] / 100) * ($wt['w'] / $totalWeight) * 100;
    }

    return (object)[
        'staff_id'         => $staff->id,
        'staff_name'       => $staff->name,
        'department'       => $staff->department->name ?? 'N/A',
        'attendance'       => round($attendanceRate, 1),
        'job_card_rate'    => round($jobCardRate, 1),
        'lesson_rate'      => $lessonRate !== null ? round($lessonRate, 1) : null,
        'lesson_total'     => $totalSubtopics,
        'lesson_covered'   => $coveredSubtopics,
        'session_rate'     => $sessionRate !== null ? round($sessionRate, 1) : null,
        'sessions_total'   => $totalSessions,
        'sessions_taught'  => $sessionsTaught,
        'score'            => round($score, 2),
    ];
});

    // Rank by performance
    $evaluations = $evaluations->sortByDesc('score')->values();

    // Department-level performance
    $departmentScores = $evaluations->groupBy('department')
        ->map(function ($rows, $dept) {
            return (object)[
                'department'    => $dept,
                'average_score' => round($rows->avg(fn($r) => $r->score), 2),
                'staff_count'   => $rows->count(),
            ];
        })
        ->sortByDesc('average_score')
        ->values();

    return view('hr-reports.evaluation', compact('evaluations', 'departmentScores'));
}






}
