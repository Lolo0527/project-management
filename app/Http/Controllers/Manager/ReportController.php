<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Generate productivity, workload & project completion reports
    public function index(Request $request)
    {
        $today = now()->toDateString();

        // 1. Company-wide Metrics
        $totalCompletedTasks = Task::where('status', 'completed')->count();
        $totalPendingTasks   = Task::where('status', '!=', 'completed')->count();
        $totalOverdueTasks   = Task::where('status', '!=', 'completed')->where('due_date', '<', $today)->count();
        $completedProjects   = Project::where('status', 'completed')->count();
        $totalProjects       = Project::count();

        // 2. Employee Workload & Productivity Report
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->withCount([
                'tasks as assigned_count',
                'tasks as completed_count' => fn($q) => $q->where('status', 'completed'),
                'tasks as pending_count'   => fn($q) => $q->where('status', '!=', 'completed'),
                'tasks as overdue_count'   => fn($q) => $q->where('status', '!=', 'completed')->where('due_date', '<', $today),
            ])
            ->get()
            ->map(function ($emp) {
                $emp->completion_rate = $emp->assigned_count > 0 
                    ? round(($emp->completed_count / $emp->assigned_count) * 100) 
                    : 0;
                return $emp;
            });

        // 3. Project Completion Percentages
        $projects = Project::with('manager')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed'),
                'tasks as overdue_tasks_count'   => fn($q) => $q->where('status', '!=', 'completed')->where('due_date', '<', $today),
            ])
            ->get()
            ->map(function ($p) {
                $p->completion_percentage = $p->tasks_count > 0 
                    ? round(($p->completed_tasks_count / $p->tasks_count) * 100) 
                    : 0;
                return $p;
            });

        return view('manager.reports.index', compact(
            'totalCompletedTasks',
            'totalPendingTasks',
            'totalOverdueTasks',
            'completedProjects',
            'totalProjects',
            'employees',
            'projects'
        ));
    }
}
