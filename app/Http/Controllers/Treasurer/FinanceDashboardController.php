<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view finance dashboard');
    }

    public function index()
    {
        $taskStats = TaskLog::select('user_id')
            ->selectRaw("COUNT(*) as total_tasks")
            ->selectRaw("SUM(CASE WHEN status = 'approved' AND submitted_at <= deadline THEN 1 ELSE 0 END) as on_time")
            ->selectRaw("SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue")
            ->selectRaw("SUM(CASE WHEN is_flagged_exceeds THEN 1 ELSE 0 END) as exceeds_flags")
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->keyBy('user_id');

        $paymentStats = Payment::select('recorded_by')
            ->selectRaw("COUNT(*) as total_payments")
            ->selectRaw("SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified")
            ->selectRaw("SUM(CASE WHEN status = 'flagged' THEN 1 ELSE 0 END) as flagged")
            ->whereNotNull('recorded_by')
            ->groupBy('recorded_by')
            ->get()
            ->keyBy('recorded_by');

        $userIds = $taskStats->keys()->merge($paymentStats->keys())->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $staffOverview = $userIds->map(function ($userId) use ($users, $taskStats, $paymentStats) {
            $tasks = $taskStats->get($userId);
            $payments = $paymentStats->get($userId);

            return [
                'user' => $users->get($userId),
                'total_tasks' => $tasks->total_tasks ?? 0,
                'on_time' => $tasks->on_time ?? 0,
                'overdue' => $tasks->overdue ?? 0,
                'exceeds_flags' => $tasks->exceeds_flags ?? 0,
                'total_payments' => $payments->total_payments ?? 0,
                'verified' => $payments->verified ?? 0,
                'flagged' => $payments->flagged ?? 0,
            ];
        });

        $complianceIssues = TaskLog::with(['user', 'justifications'])
            ->where(function ($query) {
                $query->where('is_flagged_compliance', true)
                    ->orWhereHas('justifications', fn ($q) => $q->whereNull('treasurer_reviewed_at'));
            })
            ->get();

        return view('treasurer.dashboard', compact('staffOverview', 'complianceIssues'));
    }
}
