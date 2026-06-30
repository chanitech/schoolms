<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Budget;
use App\Models\Department;
use App\Models\JobCard;
use App\Models\Leave;
use App\Models\LessonPlan;
use App\Models\Staff;
use App\Models\TimetableSessionLog;
use Illuminate\Support\Facades\Auth;

class HODDashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();

        // HOD must have a staff record with a department
        if (!$staff || !$staff->department_id) {
            return redirect()->route('dashboard')->with('error', 'No department assigned to your profile.');
        }

        $dept     = Department::with(['staff.user', 'subjects'])->find($staff->department_id);
        $deptId   = $dept->id;
        $staffIds = $dept->staff->pluck('id');
        $userIds  = $dept->staff->pluck('user_id')->filter();

        // ── Date ranges ────────────────────────────────────────────────
        $now        = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        // ══════════════════════════════════════════════════════════════
        //  STAFF OVERVIEW
        // ══════════════════════════════════════════════════════════════
        $totalStaff = $staffIds->count();

        // Attendance this month
        $attThisMonth = Attendance::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')->pluck('cnt', 'status');
        $attPresent = ($attThisMonth['present'] ?? 0) + ($attThisMonth['late'] ?? 0);
        $attAbsent  = $attThisMonth['absent'] ?? 0;
        $attTotal   = $attThisMonth->sum();
        $attRate    = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : null;

        // Attendance today per staff
        $todayAtt = Attendance::whereIn('staff_id', $staffIds)
            ->whereDate('date', $now)->pluck('status', 'staff_id');

        // Leaves
        $leavesPending  = Leave::whereIn('staff_id', $staffIds)->where('status', 'pending')->count();
        $leavesApproved = Leave::whereIn('staff_id', $staffIds)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$monthStart, $monthEnd])->count();
        $recentLeaves   = Leave::with('requester')
            ->whereIn('staff_id', $staffIds)
            ->orderByDesc('created_at')->limit(6)->get();

        // Job Cards
        $jobCards = JobCard::whereIn('assigned_to', $staffIds)->get();
        $jcPending    = $jobCards->where('status', 'pending')->count();
        $jcInProgress = $jobCards->where('status', 'in_progress')->count();
        $jcCompleted  = $jobCards->where('status', 'completed')->count();
        $recentJobs   = JobCard::with(['assignee', 'assigner'])
            ->whereIn('assigned_to', $staffIds)
            ->orderByDesc('created_at')->limit(5)->get();

        // Per-staff summary for the staff table
        $staffDetails = $dept->staff->map(function ($s) use ($monthStart, $monthEnd) {
            $att = Attendance::where('staff_id', $s->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->selectRaw('status, count(*) as cnt')->groupBy('status')
                ->pluck('cnt', 'status');
            $present = ($att['present'] ?? 0) + ($att['late'] ?? 0);
            $total   = $att->sum();
            $leaves  = Leave::where('staff_id', $s->id)->where('status', 'approved')
                ->whereBetween('start_date', [$monthStart, now()->endOfMonth()])->count();
            $jcDone  = JobCard::where('assigned_to', $s->id)->where('status', 'completed')->count();
            $jcAll   = JobCard::where('assigned_to', $s->id)->count();
            $sessionLogs = TimetableSessionLog::where('teacher_id', $s->user_id)
                ->whereBetween('session_date', [$monthStart, now()->endOfMonth()])->count();
            return [
                'staff'       => $s,
                'att_rate'    => $total > 0 ? round($present / $total * 100) : null,
                'att_days'    => $present,
                'att_total'   => $total,
                'leaves'      => $leaves,
                'jc_rate'     => $jcAll > 0 ? round($jcDone / $jcAll * 100) : null,
                'session_logs'=> $sessionLogs,
            ];
        });

        // ══════════════════════════════════════════════════════════════
        //  ACADEMIC
        // ══════════════════════════════════════════════════════════════
        $subjects = $dept->subjects;

        // Lesson plans per subject in this dept
        $lessonPlans = LessonPlan::with(['topics.subtopics', 'teacher', 'schoolClass'])
            ->whereHas('subject', fn($q) => $q->where('department_id', $deptId))
            ->get();

        $totalPlans    = $lessonPlans->count();
        $totalSubtopics = 0;
        $coveredSubtopics = 0;
        foreach ($lessonPlans as $plan) {
            $stats = $plan->completionStats();
            $totalSubtopics   += $stats['total'];
            $coveredSubtopics += $stats['covered'];
        }
        $overallCoverage = $totalSubtopics > 0
            ? round($coveredSubtopics / $totalSubtopics * 100)
            : 0;

        // Per-plan stats for the academic table
        $planStats = $lessonPlans->map(function ($plan) {
            $s = $plan->completionStats();
            return [
                'plan'      => $plan,
                'total'     => $s['total'],
                'covered'   => $s['covered'],
                'pct'       => $s['pct'],
            ];
        })->sortBy('pct')->values();

        // Session logs this month
        $sessionLogs = TimetableSessionLog::whereIn('teacher_id', $userIds)
            ->whereBetween('session_date', [$monthStart, $monthEnd])->get();
        $sessTotal    = $sessionLogs->count();
        $sessAttended = $sessionLogs->whereIn('status', ['attended', 'late'])->count();
        $sessAbsent   = $sessionLogs->where('status', 'absent')->count();
        $sessWithTopic = $sessionLogs->whereNotNull('lesson_topic_id')->count();
        $sessRate     = $sessTotal > 0 ? round($sessAttended / $sessTotal * 100) : null;
        $topicRate    = $sessTotal > 0 ? round($sessWithTopic / $sessTotal * 100) : null;

        $recentSessions = TimetableSessionLog::with(['teacher', 'subject', 'schoolClass'])
            ->whereIn('teacher_id', $userIds)
            ->orderByDesc('session_date')->limit(8)->get();

        // ══════════════════════════════════════════════════════════════
        //  FINANCIAL
        // ══════════════════════════════════════════════════════════════
        $budgets = Budget::with('items')
            ->where('department_id', $deptId)
            ->orderByDesc('created_at')->get();

        $budgetTotal    = $budgets->sum('total_amount');
        $budgetApproved = $budgets->whereIn('status', ['approved', 'in_use', 'completed'])->sum('total_amount');
        $budgetPending  = $budgets->where('status', 'pending')->sum('total_amount');

        $budgetByStatus = $budgets->groupBy('status')->map->count();
        $recentBudgets  = $budgets->take(5);

        return view('hod.dashboard', compact(
            'dept', 'totalStaff', 'staffDetails', 'todayAtt',
            'attPresent', 'attAbsent', 'attTotal', 'attRate',
            'leavesPending', 'leavesApproved', 'recentLeaves',
            'jcPending', 'jcInProgress', 'jcCompleted', 'recentJobs',
            'subjects', 'totalPlans', 'overallCoverage',
            'coveredSubtopics', 'totalSubtopics', 'planStats',
            'sessTotal', 'sessAttended', 'sessAbsent', 'sessWithTopic',
            'sessRate', 'topicRate', 'sessionLogs', 'recentSessions',
            'budgetTotal', 'budgetApproved', 'budgetPending',
            'budgetByStatus', 'recentBudgets', 'now', 'monthStart'
        ));
    }
}
