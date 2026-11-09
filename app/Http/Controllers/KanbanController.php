<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    // Display the Kanban board
    public function index(Request $request)
    {
        $user = auth()->user();
        $projectId = $request->query('project_id');

        if ($user->role === 'employee') {
            // Employees can only see their assigned tasks
            $query = Task::with(['project', 'assignee'])->where('assigned_to', $user->id);
            $projects = Project::whereHas('tasks', fn($q) => $q->where('assigned_to', $user->id))
                ->orWhereHas('members', fn($q) => $q->where('users.id', $user->id))
                ->orderBy('title')
                ->get();
        } elseif ($user->role === 'manager') {
            // Managers can see tasks belonging to their projects
            $query = Task::with(['project', 'assignee'])->whereHas('project', function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id));
            });
            $projects = Project::where('manager_id', $user->id)
                ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id))
                ->orderBy('title')
                ->get();
        } else {
            // Admins have access to all system modules
            $query = Task::with(['project', 'assignee']);
            $projects = Project::orderBy('title')->get();
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        // Group tasks into the 3 columns
        $todoTasks       = (clone $query)->where('status', 'pending')->get();
        $inProgressTasks = (clone $query)->where('status', 'in_progress')->get();
        $completedTasks  = (clone $query)->where('status', 'completed')->get();

        return view('kanban.index', compact(
            'todoTasks',
            'inProgressTasks',
            'completedTasks',
            'projects',
            'projectId'
        ));
    }

    // Update task status via Drag & Drop or button
    public function updateStatus(Request $request, Task $task)
    {
        $user = auth()->user();

        // Enforce permissions: employees can only move their own tasks; managers only within their projects
        if ($user->role === 'employee') {
            if ($task->assigned_to !== $user->id) {
                abort(403, 'Unauthorized. You can only modify your own assigned tasks.');
            }
        } elseif ($user->role === 'manager') {
            $project = $task->project;
            $isAuthorized = $project && ($project->manager_id === $user->id || $project->members->contains($user->id));
            if (!$isAuthorized) {
                abort(403, 'Unauthorized. You can only modify tasks belonging to your projects.');
            }
        }

        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update([
            'status'      => $data['status'],
            'is_approved' => ($data['status'] === 'completed') ? $task->is_approved : false,
        ]);

        // Trigger Notification on status change
        $actor = $user;
        $recipientId = ($actor->id === $task->assigned_to)
            ? ($task->project->manager_id ?? null)
            : $task->assigned_to;

        if ($recipientId && $recipientId !== $actor->id) {
            $recipient = \App\Models\User::find($recipientId);
            $readableStatus = ucfirst(str_replace('_', ' ', $task->status));
            $recipient?->notify(new \App\Notifications\TaskNotification(
                'Task Status Changed',
                "Task \"{$task->title}\" was moved to {$readableStatus} by {$actor->name}.",
                route('tasks.show', $task),
                '⚡',
                'task_status_changed'
            ));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'status'  => $task->status,
                'message' => "Task \"{$task->title}\" moved to " . ucfirst(str_replace('_', ' ', $task->status)),
            ]);
        }

        return back()->with('success', "Task status updated to " . ucfirst(str_replace('_', ' ', $task->status)));
    }
}
