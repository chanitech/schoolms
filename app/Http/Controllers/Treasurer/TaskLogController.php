<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\TaskLog;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskLogController extends Controller
{
    private const FINANCE_ROLES = [
        'treasurer', 'chief-accountant', 'accountant', 'class_accountant',
        'procurement_officer', 'cashier', 'storekeeper',
    ];

    public function __construct()
    {
        // 'index' is deliberately NOT gated by a specific permission — its
        // own logic already shows the treasurer/managers everything and
        // everyone else only their own assigned tasks. Gating it behind
        // 'manage tasks' would block roles like
        // class_accountant/chief-accountant/accountant (who don't have that
        // permission) from viewing tasks assigned to them. It's still
        // restricted to Finance Office membership so roles like Teacher
        // can't reach it at all.
        $this->middleware('can:is-finance-office')->only('index');
        $this->middleware('permission:manage tasks')->only(['create', 'store', 'approve', 'toggleExceeds']);
    }

    /**
     * Treasurer sees all Finance Office tasks; other roles see only their own.
     */
    public function index()
    {
        $user = Auth::user();

        $tasks = $user->can('view finance dashboard')
            ? TaskLog::with(['user', 'justifications'])->latest('deadline')->get()
            : TaskLog::with(['user', 'justifications'])->where('user_id', $user->id)->latest('deadline')->get();

        return view('treasurer.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::role(self::FINANCE_ROLES)->get();

        return view('treasurer.tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|max:255',
            'task_description' => 'required|string|max:2000',
            'deadline' => 'required|date|after:now',
        ]);

        $task = TaskLog::create([
            ...$validated,
            'status' => 'pending',
        ]);

        $task->user->notify(new SystemAlertNotification(
            title: 'New task assigned',
            message: $task->task_description,
            url: route('treasurer.tasks.index'),
            icon: 'fas fa-tasks',
            color: 'primary',
        ));

        return redirect()->route('treasurer.tasks.index')->with('success', 'Task assigned.');
    }

    public function updateProgress(Request $request, TaskLog $taskLog)
    {
        $this->authorizeOwner($taskLog);

        $validated = $request->validate([
            'percent_complete' => 'required|integer|min:0|max:100',
        ]);

        $taskLog->update([
            'percent_complete' => $validated['percent_complete'],
            'status' => $validated['percent_complete'] >= 100 ? $taskLog->status : 'in_progress',
        ]);

        return back()->with('success', 'Progress updated.');
    }

    public function submitForReview(TaskLog $taskLog)
    {
        $this->authorizeOwner($taskLog);

        $taskLog->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'percent_complete' => 100,
        ]);

        return back()->with('success', 'Submitted for Treasurer review.');
    }

    public function approve(TaskLog $taskLog)
    {
        $taskLog->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $taskLog->user->notify(new SystemAlertNotification(
            title: 'Task approved',
            message: "Your task \"{$taskLog->task_description}\" was approved by the Treasurer.",
            url: route('treasurer.tasks.index'),
            icon: 'fas fa-check-circle',
            color: 'success',
        ));

        return back()->with('success', 'Task approved.');
    }

    public function toggleExceeds(TaskLog $taskLog)
    {
        $taskLog->update(['is_flagged_exceeds' => !$taskLog->is_flagged_exceeds]);

        return back()->with('success', 'Recognition flag updated.');
    }

    private function authorizeOwner(TaskLog $taskLog): void
    {
        if ($taskLog->user_id !== Auth::id()) {
            abort(403, 'This task is not assigned to you.');
        }
    }
}
