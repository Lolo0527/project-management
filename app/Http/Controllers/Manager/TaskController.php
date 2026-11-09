<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Show task creation form for a project
    public function create(Project $project)
    {
        // Prefer project's team members, fallback to active employees
        $employees = $project->members->count() > 0 
            ? $project->members 
            : User::where('role', 'employee')->where('is_active', true)->get();

        return view('manager.tasks.create', compact('project', 'employees'));
    }

    // Save task & notify assigned employee
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority'    => 'required|in:low,medium,high',
            'start_date'  => 'nullable|date',
            'due_date'    => 'required|date',
            'status'      => 'required|in:pending,in_progress,completed',
            'attachment'  => 'nullable|file|max:10240',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $task = $project->tasks()->create([
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'assigned_to'     => $data['assigned_to'] ?? null,
            'priority'        => $data['priority'],
            'start_date'      => $data['start_date'] ?? null,
            'due_date'        => $data['due_date'],
            'status'          => $data['status'],
            'attachment_path' => $attachmentPath,
        ]);

        // Send notification to employee
        if ($task->assigned_to) {
            $employee = User::find($task->assigned_to);
            $employee?->notify(new \App\Notifications\TaskNotification(
                'New Task Assigned',
                "You were assigned to task \"{$task->title}\" in {$project->title}.",
                route('tasks.show', $task),
                '📋',
                'task_assigned'
            ));
        }

        return redirect()->route('manager.projects.show', $project)
            ->with('success', "Task \"{$task->title}\" assigned successfully!");
    }
}
