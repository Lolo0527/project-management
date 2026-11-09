<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Show the project creation form
    public function create()
    {
        $managers  = User::whereIn('role', ['manager', 'admin'])->where('is_active', true)->get();
        $employees = User::where('role', 'employee')->where('is_active', true)->get();

        return view('manager.projects.create', compact('managers', 'employees'));
    }

    // Save newly created project into the database
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_date'     => 'required|date',
            'deadline'       => 'required|date|after_or_equal:start_date',
            'manager_id'     => 'required|exists:users,id',
            'priority'       => 'required|in:low,medium,high',
            'team_members'   => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date'  => $data['start_date'],
            'deadline'    => $data['deadline'],
            'manager_id'  => $data['manager_id'],
            'priority'    => $data['priority'],
            'status'      => 'active',
        ]);

        if (!empty($data['team_members'])) {
            $project->members()->attach($data['team_members']);
        }

        return redirect()->route('manager.projects.show', $project)
            ->with('success', 'Project created successfully!');
    }

    // View project details and its tasks
    public function show(Project $project)
    {
        $project->load(['tasks.assignee', 'manager', 'members']);

        return view('manager.projects.show', compact('project'));
    }

    // Show form to edit an existing project
    public function edit(Project $project)
    {
        $managers  = User::whereIn('role', ['manager', 'admin'])->where('is_active', true)->get();
        $employees = User::where('role', 'employee')->where('is_active', true)->get();

        return view('manager.projects.edit', compact('project', 'managers', 'employees'));
    }

    // Update project attributes and team members
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_date'     => 'required|date',
            'deadline'       => 'required|date|after_or_equal:start_date',
            'manager_id'     => 'required|exists:users,id',
            'priority'       => 'required|in:low,medium,high',
            'status'         => 'required|in:active,completed',
            'team_members'   => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        ]);

        $oldDeadline = $project->deadline;

        $project->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date'  => $data['start_date'],
            'deadline'    => $data['deadline'],
            'manager_id'  => $data['manager_id'],
            'priority'    => $data['priority'],
            'status'      => $data['status'],
        ]);

        $project->members()->sync($data['team_members'] ?? []);

        // If deadline changed, notify team members
        if ($oldDeadline !== $project->deadline) {
            foreach ($project->members as $member) {
                $member->notify(new \App\Notifications\TaskNotification(
                    'Project Deadline Changed',
                    "The deadline for project \"{$project->title}\" was updated to {$project->deadline}.",
                    route('manager.projects.show', $project),
                    '📅',
                    'project_deadline_changed'
                ));
            }
        }

        return redirect()->route('manager.projects.show', $project)
            ->with('success', 'Project details updated successfully!');
    }

    // Update project deadline and notify team members
    public function updateDeadline(Request $request, Project $project)
    {
        $data = $request->validate([
            'deadline' => 'required|date',
        ]);

        $project->update(['deadline' => $data['deadline']]);

        foreach ($project->members as $member) {
            $member->notify(new \App\Notifications\TaskNotification(
                'Project Deadline Changed',
                "The deadline for project \"{$project->title}\" was updated to {$project->deadline}.",
                route('manager.projects.show', $project),
                '📅',
                'project_deadline_changed'
            ));
        }

        return back()->with('success', 'Project deadline updated and team members notified.');
    }

    // Close and complete project once all tasks are approved
    public function closeProject(Project $project)
    {
        if ($project->tasks()->count() === 0) {
            return back()->withErrors(['error' => 'Cannot close an empty project with no tasks.']);
        }

        $unapprovedTasksCount = $project->tasks()->where('is_approved', false)->count();

        if ($unapprovedTasksCount > 0) {
            return back()->withErrors(['error' => "Cannot close project. There are still {$unapprovedTasksCount} task(s) pending completion or manager approval."]);
        }

        $project->update(['status' => 'completed']);

        // Notify all team members
        foreach ($project->members as $member) {
            $member->notify(new \App\Notifications\TaskNotification(
                'Project Completed! 🏆',
                "Project \"{$project->title}\" has been fully completed and closed by the manager.",
                route('manager.projects.show', $project),
                '🏆',
                'project_completed'
            ));
        }

        return back()->with('success', "Project \"{$project->title}\" has been successfully closed and marked Completed!");
    }
}
