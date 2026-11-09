<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        return view('dashboards.manager', [
            'totalEmployees'    => User::where('role', 'employee')->count(),
            'totalProjects'     => Project::count(),
            'activeProjects'    => Project::where('status', 'active')->count(),
            'completedProjects' => Project::where('status', 'completed')->count(),
            'pendingTasks'      => Task::where('status', '!=', 'completed')->count(),
            'overdueTasks'      => Task::where('status', '!=', 'completed')->where('due_date', '<', $today)->count(),
            'employeeStats'     => User::where('role', 'employee')->withCount(['tasks', 'completedTasks'])->get(),
            'upcomingDeadlines' => Task::with('project')
                                    ->where('status', '!=', 'completed')
                                    ->where('due_date', '>=', $today)
                                    ->orderBy('due_date', 'asc')
                                    ->take(5)
                                    ->get(),
            'projects'          => Project::withCount('tasks')->get(),
        ]);
    }
}
