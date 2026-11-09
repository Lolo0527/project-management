<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'assigned_to', 'title', 'description', 'status', 'is_approved', 'priority', 'start_date', 'due_date', 'attachment_path', 'submission_notes'])]
class Task extends Model
{
    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }
    // A task belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // A task is assigned to an employee user
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Communication thread / comments on this task
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }
}
