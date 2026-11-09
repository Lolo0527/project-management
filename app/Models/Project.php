<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'start_date', 'deadline', 'manager_id', 'priority', 'status'])]
class Project extends Model
{
    // A project has many tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // The assigned Project Manager
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Team members assigned to this project
    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    // Check if all project tasks have been completed and approved by a manager
    public function allTasksApproved(): bool
    {
        $total = $this->tasks()->count();
        return $total > 0 && $this->tasks()->where('is_approved', false)->count() === 0;
    }
}
