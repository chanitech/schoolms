<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\JobCard;
use App\Models\Event;
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

    // 2️⃣ Aggregate Leaves per staff
    $leaveData = Leave::selectRaw('staff_id, COUNT(*) as leaves_taken')
        ->groupBy('staff_id')
        ->pluck('leaves_taken', 'staff_id');

    // 3️⃣ Aggregate JobCard completion per staff
    $jobCardData = JobCard::selectRaw('assigned_to, 
        SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) as completed, 
        COUNT(*) as total')
        ->groupBy('assigned_to')
        ->pluck('completed', 'assigned_to');

    $jobCardTotal = JobCard::selectRaw('assigned_to, COUNT(*) as total')
        ->groupBy('assigned_to')
        ->pluck('total', 'assigned_to');

    // 4️⃣ Map staff to evaluations
    $evaluations = $staffList->map(function($staff) use ($attendanceData, $attendanceTotal, $jobCardData, $jobCardTotal) {

    $present = $attendanceData[$staff->id] ?? 0;
    $totalAttendance = $attendanceTotal[$staff->id] ?? 0;
    $attendanceRate = $totalAttendance > 0 ? ($present / $totalAttendance) * 100 : 0;

    $completed = $jobCardData[$staff->id] ?? 0;
    $totalJobCards = $jobCardTotal[$staff->id] ?? 0;
    $jobCardRate = $totalJobCards > 0 ? ($completed / $totalJobCards) * 100 : 0;

    // 50% attendance + 50% job card
    $score = ($attendanceRate * 0.5) + ($jobCardRate * 0.5);

    return (object)[
        'staff_id' => $staff->id,
        'staff_name' => $staff->name,
        'department' => $staff->department->name ?? 'N/A',
        'attendance' => round($attendanceRate, 1),
        'job_card_rate' => round($jobCardRate, 1),
        'score' => round($score, 2),
    ];
});

    // Rank by performance
    $evaluations = $evaluations->sortByDesc('score')->values();

    // Department-level performance
    $departmentScores = $evaluations->groupBy('department')
        ->map(function ($rows, $dept) {
            return (object)[
                'department' => $dept,
                'average_score' => round($rows->avg(fn($r) => $r->score), 2),
            ];
        })
        ->sortByDesc('average_score')
        ->values();

    return view('hr-reports.evaluation', compact('evaluations', 'departmentScores'));
}






}
