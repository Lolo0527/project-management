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
        $user = auth()->user();
        $isManager = $user->role === 'manager';

        // Scoped to manager's projects or company-wide for admins
        $projectQuery = $isManager ? Project::where('manager_id', $user->id) : Project::query();
        $taskQuery = $isManager 
            ? Task::whereHas('project', fn($q) => $q->where('manager_id', $user->id)) 
            : Task::query();

        return view('dashboards.manager', [
            'totalEmployees'    => User::where('role', 'employee')->where('is_active', true)->count(),
            'totalProjects'     => (clone $projectQuery)->count(),
            'activeProjects'    => (clone $projectQuery)->where('status', 'active')->count(),
            'completedProjects' => (clone $projectQuery)->where('status', 'completed')->count(),
            'pendingTasks'      => (clone $taskQuery)->where('status', '!=', 'completed')->count(),
            'overdueTasks'      => (clone $taskQuery)->where('status', '!=', 'completed')->where('due_date', '<', $today)->count(),
            'employeeStats'     => User::where('role', 'employee')->where('is_active', true)->withCount(['tasks', 'completedTasks'])->get(),
            'upcomingDeadlines' => (clone $taskQuery)->with('project')
                                    ->where('status', '!=', 'completed')
                                    ->where('due_date', '>=', $today)
                                    ->orderBy('due_date', 'asc')
                                    ->take(5)
                                    ->get(),
            'projects'          => (clone $projectQuery)->withCount('tasks')->get(),
        ]);
    }
}
