<?php
// app/Console/Commands/MarkOverdueTasks.php

namespace App\Console\Commands;

use App\Models\TaskLog;
use App\Notifications\SystemAlertNotification;
use Illuminate\Console\Command;

class MarkOverdueTasks extends Command
{
    protected $signature = 'finance:mark-overdue-tasks';
    protected $description = 'Mark past-deadline Finance Office task logs as overdue and notify the assignee';

    public function handle()
    {
        $tasks = TaskLog::withoutSchoolScope()
            ->whereNotIn('status', ['approved', 'overdue'])
            ->where('deadline', '<', now())
            ->get();

        foreach ($tasks as $task) {
            $task->update(['status' => 'overdue']);

            $task->user->notify(new SystemAlertNotification(
                title: 'Task overdue',
                message: "Your task \"{$task->task_description}\" missed its deadline. Please submit a justification.",
                url: route('treasurer.tasks.index'),
                icon: 'fas fa-exclamation-triangle',
                color: 'danger',
            ));
        }

        $this->info("Marked {$tasks->count()} tasks as overdue.");
    }
}
