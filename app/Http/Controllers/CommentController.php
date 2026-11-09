<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Store comment and optional PDF / image attachment
    public function store(Request $request, Task $task)
    {
        $user = auth()->user();

        // Enforce access control on task comments
        if ($user->role === 'employee') {
            $isAssigned = $task->assigned_to === $user->id;
            $isMember = $task->project && $task->project->members->contains($user->id);
            if (!$isAssigned && !$isMember) {
                abort(403, 'Unauthorized. You do not have access to comment on this task.');
            }
        } elseif ($user->role === 'manager') {
            $project = $task->project;
            $isAuthorized = $project && ($project->manager_id === $user->id || $project->members->contains($user->id));
            if (!$isAuthorized) {
                abort(403, 'Unauthorized. You do not manage this project.');
            }
        }

        $request->validate([
            'content'    => 'required_without:attachment|nullable|string',
            'attachment' => 'nullable|file|max:10240', // Max 10MB
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('comment_attachments', 'public');
        }

        $task->comments()->create([
            'user_id'         => $user->id,
            'content'         => $request->input('content') ?? 'Shared an attachment.',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        $author = $user;
        $recipientId = ($author->id === $task->assigned_to) 
            ? ($task->project->manager_id ?? null) 
            : $task->assigned_to;

        if ($recipientId && $recipientId !== $author->id) {
            $recipient = \App\Models\User::find($recipientId);
            $recipient?->notify(new \App\Notifications\TaskNotification(
                'New Comment on Task',
                "{$author->name} commented on \"{$task->title}\".",
                route('tasks.show', $task),
                '💬',
                'task_commented'
            ));
        }

        return back()->with('success', 'Message posted to task thread.');
    }
}
