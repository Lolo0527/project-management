<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'department', 'is_active', 'invitation_token', 'invitation_expires_at'])]
#[Hidden(['password', 'remember_token', 'invitation_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'invitation_expires_at' => 'datetime',
        ];
    }

    // Check if invitation token is valid and unexpired
    public function hasValidInvitation(): bool
    {
        return !empty($this->invitation_token) &&
               $this->invitation_expires_at &&
               $this->invitation_expires_at->isFuture();
    }

    // Tasks assigned to this user
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // Completed tasks for productivity metrics
    public function completedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to')->where('status', 'completed');
    }

    // Projects this user is a member of
    public function assignedProjects()
    {
        return $this->belongsToMany(Project::class);
    }
}
