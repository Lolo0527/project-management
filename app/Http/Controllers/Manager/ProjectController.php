<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Authorize that current user is the project manager or admin
    protected function authorizeProjectManager(Project $project, bool $allowMembers = false): void
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return;
        }

        if ($allowMembers) {
            $isAuthorized = ($project->manager_id === $user->id) || $project->members->contains($user->id);
            if (!$isAuthorized) {
                abort(403, 'Unauthorized. You do not manage this project.');
            }
        } else {
            if ($project->manager_id !== $user->id) {
                abort(403, 'Unauthorized. You can only modify projects you manage.');
            }
        }
    }

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

        // Validate that project manager has the correct role (manager or admin)
        $manager = User::findOrFail($data['manager_id']);
        if (!in_array($manager->role, ['manager', 'admin'])) {
            return back()->withInput()->withErrors(['manager_id' => 'The assigned project manager must have the manager or admin role.']);
        }

        // Validate that all assigned team members are active employees
        if (!empty($data['team_members'])) {
            $validMembersCount = User::whereIn('id', $data['team_members'])
                ->where('role', 'employee')
                ->where('is_active', true)
                ->count();

            if ($validMembersCount !== count($data['team_members'])) {
                return back()->withInput()->withErrors(['team_members' => 'All assigned team members must be active employees.']);
            }
        }

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
        $this->authorizeProjectManager($project, true);

        $project->load(['tasks.assignee', 'manager', 'members']);

        return view('manager.projects.show', compact('project'));
    }

    // Show form to edit an existing project
    public function edit(Project $project)
    {
        $this->authorizeProjectManager($project, false);

        $managers  = User::whereIn('role', ['manager', 'admin'])->where('is_active', true)->get();
        $employees = User::where('role', 'employee')->where('is_active', true)->get();

        return view('manager.projects.edit', compact('project', 'managers', 'employees'));
    }

    // Update project attributes and team members
    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectManager($project, false);

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

        // Validate that project manager has the correct role
        $manager = User::findOrFail($data['manager_id']);
        if (!in_array($manager->role, ['manager', 'admin'])) {
            return back()->withInput()->withErrors(['manager_id' => 'The assigned project manager must have the manager or admin role.']);
        }

        // Validate that assigned team members are active employees
        if (!empty($data['team_members'])) {
            $validMembersCount = User::whereIn('id', $data['team_members'])
                ->where('role', 'employee')
                ->where('is_active', true)
                ->count();

            if ($validMembersCount !== count($data['team_members'])) {
                return back()->withInput()->withErrors(['team_members' => 'All assigned team members must be active employees.']);
            }
        }

        // Prevent direct project completion without completing/approving all tasks
        if ($data['status'] === 'completed' && $project->status !== 'completed') {
            if (!$project->allTasksApproved()) {
                return back()->withInput()->withErrors([
                    'status' => 'Cannot mark project as completed directly. All project tasks must first be completed and approved by the manager.'
                ]);
            }
        }

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
        $this->authorizeProjectManager($project, false);

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
        $this->authorizeProjectManager($project, false);

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
