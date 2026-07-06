<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\AccountantClassAssignment;
use App\Models\Budget;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\JobDescription;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\ProcurementRequest;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FinanceDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view finance dashboard')->only(['index']);
    }

    /**
     * Treasurer's whole-office overview: every Treasurer Office module in one place,
     * not just the new Finance Office additions.
     */
    public function index()
    {
        $officeSummary = [
            'pending_loans' => Loan::where('status', 'pending')->count(),
            'pending_budgets' => Budget::whereIn('status', ['pending', 'partially_approved'])->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'pending_procurement' => ProcurementRequest::where('status', 'pending')->count(),
            'payments_needing_review' => Payment::whereIn('status', ['pending', 'flagged'])->count(),
            'overdue_tasks' => TaskLog::where('status', 'overdue')->count(),
        ];

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

        return view('treasurer.dashboard', compact('officeSummary', 'staffOverview', 'complianceIssues'));
    }

    /**
     * Personal dashboard for any Finance Office member — their own tasks,
     * job description(s), and whatever is relevant to their specific role.
     * No permission gate: it only ever shows the logged-in user's own data.
     */
    public function myDashboard()
    {
        $user = Auth::user();

        $tasks = TaskLog::with('justifications')
            ->where('user_id', $user->id)
            ->latest('deadline')
            ->get();

        $jobDescriptions = JobDescription::whereIn('role_name', $user->getRoleNames())->get();

        $assignedClasses = AccountantClassAssignment::where('user_id', $user->id)
            ->with('schoolClass')
            ->get();

        $pendingClassPayments = $assignedClasses->isNotEmpty()
            ? Payment::whereIn('class_id', $assignedClasses->pluck('class_id'))
                ->whereIn('status', ['pending', 'flagged'])
                ->count()
            : null;

        $myProcurementRequests = ProcurementRequest::where('requested_by', $user->id)
            ->latest()
            ->get();

        $awaitingDisbursement = $user->can('disburse payments')
            ? ProcurementRequest::where('status', 'approved')->count()
            : null;

        $lowStockCount = $user->can('manage stock')
            ? InventoryItem::all()->filter(fn (InventoryItem $item) => $item->isLowStock())->count()
            : null;

        return view('treasurer.my-dashboard', compact(
            'tasks', 'jobDescriptions', 'assignedClasses', 'pendingClassPayments',
            'myProcurementRequests', 'awaitingDisbursement', 'lowStockCount'
        ));
    }
}
