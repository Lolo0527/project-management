<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function admin()
    {
        $totalUsers    = \App\Models\User::count();
        $totalProjects = \App\Models\Project::count();
        $totalTasks    = \App\Models\Task::count();
        $recentUsers   = \App\Models\User::latest()->take(5)->get();

        return view('dashboards.admin', compact('totalUsers', 'totalProjects', 'totalTasks', 'recentUsers'));
    }

    public function manager()
    {
        return view('dashboards.manager');
    }

    public function employee()
    {
        $user = auth()->user();
        $tasks = $user->tasks()->with('project')->orderBy('due_date', 'asc')->get();
        $projects = $user->assignedProjects()->with('manager')->get();
        $notifications = $user->notifications()->take(5)->get();

        return view('dashboards.employee', compact('tasks', 'projects', 'notifications'));
    }
}
