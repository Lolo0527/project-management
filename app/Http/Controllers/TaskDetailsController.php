<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskDetailsController extends Controller
{
    // Authorize user access to this task
    protected function authorizeTaskAccess(Task $task): void
    {
        $user = auth()->user();

        if ($user->role === 'employee') {
            $isAssigned = $task->assigned_to === $user->id;
            $isMember = $task->project && $task->project->members->contains($user->id);
            if (!$isAssigned && !$isMember) {
                abort(403, 'Unauthorized access to this task.');
            }
        } elseif ($user->role === 'manager') {
            $project = $task->project;
            $isAuthorized = $project && ($project->manager_id === $user->id || $project->members->contains($user->id));
            if (!$isAuthorized) {
                abort(403, 'Unauthorized access to this task.');
            }
        }
    }

    // Display task details and comment communication feed
    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load(['project.members', 'assignee', 'comments.user']);

        return view('tasks.show', compact('task'));
    }

    // Employee submits completed work for review
    public function submitForReview(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);
        $user = auth()->user();

        if ($user->role === 'employee' && $task->assigned_to !== $user->id) {
            abort(403, 'Unauthorized. You can only submit your own tasks.');
        }

        $data = $request->validate([
            'submission_notes' => 'nullable|string|max:1000',
            'deliverable'      => 'nullable|file|max:10240',
        ]);

        $deliverablePath = null;
        $deliverableName = null;
        if ($request->hasFile('deliverable')) {
            $file = $request->file('deliverable');
            $deliverableName = $file->getClientOriginalName();
            $deliverablePath = $file->store('comment_attachments', 'public');
        }

        $task->update([
            'status'           => 'completed',
            'is_approved'      => false,
            'submission_notes' => $data['submission_notes'] ?? null,
        ]);

        // Post submission note to thread
        $note = "🚀 WORK SUBMITTED FOR REVIEW by {$user->name}." . (!empty($task->submission_notes) ? "\nNotes: " . $task->submission_notes : '');
        $task->comments()->create([
            'user_id'         => $user->id,
            'content'         => $note,
            'attachment_path' => $deliverablePath,
            'attachment_name' => $deliverableName,
        ]);

        // Notify project manager
        $managerId = $task->project->manager_id ?? null;
        if ($managerId) {
            $manager = \App\Models\User::find($managerId);
            $manager?->notify(new \App\Notifications\TaskNotification(
                'Work Submitted for Review 📋',
                "{$user->name} submitted work on task \"{$task->title}\" for review.",
                route('tasks.show', $task),
                '📋',
                'task_submitted'
            ));
        }

        return back()->with('success', "Task \"{$task->title}\" submitted for manager review!");
    }

    // Manager marks task as approved
    public function approve(Task $task)
    {
        $this->authorizeTaskAccess($task);
        $user = auth()->user();

        if (!in_array($user->role, ['manager', 'admin'])) {
            abort(403, 'Only managers or administrators can approve tasks.');
        }

        $task->update([
            'status'      => 'completed',
            'is_approved' => true,
        ]);

        if ($task->assigned_to) {
            $employee = \App\Models\User::find($task->assigned_to);
            $employee?->notify(new \App\Notifications\TaskNotification(
                'Task Approved! 🎉',
                "Your work on task \"{$task->title}\" was reviewed and approved by manager.",
                route('tasks.show', $task),
                '✅',
                'task_approved'
            ));
        }

        return back()->with('success', "Task \"{$task->title}\" marked as approved!");
    }

    // Manager requests corrections / revisions on submitted work
    public function requestCorrection(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);
        $user = auth()->user();

        if (!in_array($user->role, ['manager', 'admin'])) {
            abort(403, 'Only managers or administrators can request corrections.');
        }

        $data = $request->validate([
            'revision_notes' => 'required|string|max:1000',
        ]);

        $task->update([
            'status'      => 'in_progress',
            'is_approved' => false,
        ]);

        // Post correction feedback to comments thread
        $task->comments()->create([
            'user_id' => $user->id,
            'content' => "⚠️ CORRECTION REQUESTED by {$user->name}:\n" . $data['revision_notes'],
        ]);

        if ($task->assigned_to) {
            $employee = \App\Models\User::find($task->assigned_to);
            $employee?->notify(new \App\Notifications\TaskNotification(
                'Correction Requested ⚠️',
                "Manager {$user->name} requested revisions on \"{$task->title}\": {$data['revision_notes']}",
                route('tasks.show', $task),
                '⚠️',
                'task_revision_requested'
            ));
        }

        return back()->with('success', "Correction requested. Task moved back to In Progress.");
    }
}
