<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Console\Command;

class CheckTaskDeadlines extends Command
{
    protected $signature = 'tasks:check-deadlines';
    protected $description = 'Check approaching task deadlines within 48 hours and notify assignees';

    public function handle()
    {
        $today = now()->toDateString();
        $soon  = now()->addDays(2)->toDateString();

        $tasks = Task::with('assignee')
            ->where('status', '!=', 'completed')
            ->whereBetween('due_date', [$today, $soon])
            ->whereNotNull('assigned_to')
            ->get();

        foreach ($tasks as $task) {
            $task->assignee?->notify(new TaskNotification(
                'Deadline Approaching ⏰',
                "Task \"{$task->title}\" is due soon on {$task->due_date}.",
                route('tasks.show', $task),
                '⏰',
                'deadline_approaching'
            ));
        }

        $this->info("Checked deadlines. Notified {$tasks->count()} employee(s).");
    }
}
