<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\TaskJustification;
use App\Models\TaskLog;
use App\Notifications\SystemAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskJustificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:submit task justification')->only(['store']);
        $this->middleware('permission:review task justification')->only(['review']);
    }

    public function store(Request $request, TaskLog $taskLog)
    {
        if ($taskLog->user_id !== Auth::id()) {
            abort(403, 'This task is not assigned to you.');
        }

        if ($taskLog->status !== 'overdue') {
            return back()->with('error', 'A justification is only required for overdue tasks.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        TaskJustification::create([
            'task_log_id' => $taskLog->id,
            'submitted_by' => Auth::id(),
            'reason' => $validated['reason'],
            'submitted_at' => now(),
        ]);

        // Every Treasurer permission-holder should hear about this; keep it
        // simple and notify whoever assigned/approves the task.
        if ($taskLog->approvedBy) {
            $taskLog->approvedBy->notify(new SystemAlertNotification(
                title: 'Justification submitted',
                message: "{$taskLog->user->name} submitted a justification for a missed deadline.",
                url: route('treasurer.tasks.index'),
                icon: 'fas fa-file-alt',
                color: 'warning',
            ));
        }

        return back()->with('success', 'Justification submitted to the Treasurer.');
    }

    public function review(TaskJustification $justification)
    {
        $justification->update(['treasurer_reviewed_at' => now()]);

        return back()->with('success', 'Justification marked as reviewed.');
    }
}
