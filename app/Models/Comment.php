<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['task_id', 'user_id', 'content', 'attachment_path', 'attachment_name'])]
class Comment extends Model
{
    // Comment belongs to a task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Comment belongs to an author (user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
