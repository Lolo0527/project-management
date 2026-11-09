<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'project_id'   => $this->task->project_id,
            'project_name' => $this->task->project->title ?? 'Project',
            'priority'     => $this->task->priority,
            'due_date'     => $this->task->due_date,
            'message'      => "You have been assigned to task: \"{$this->task->title}\" in {$this->task->project->title}.",
        ];
    }
}
