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
        $user = auth()->user();
        $isManager = $user->role === 'manager';

        // Scope queries to manager's projects or company-wide for admins
        $projectQuery = $isManager ? Project::where('manager_id', $user->id) : Project::query();
        $taskQuery = $isManager 
            ? Task::whereHas('project', fn($q) => $q->where('manager_id', $user->id)) 
            : Task::query();

        // 1. Metrics
        $totalCompletedTasks = (clone $taskQuery)->where('status', 'completed')->count();
        $totalPendingTasks   = (clone $taskQuery)->where('status', '!=', 'completed')->count();
        $totalOverdueTasks   = (clone $taskQuery)->where('status', '!=', 'completed')->where('due_date', '<', $today)->count();
        $completedProjects   = (clone $projectQuery)->where('status', 'completed')->count();
        $totalProjects       = (clone $projectQuery)->count();

        // 2. Employee Workload & Productivity Report
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->withCount([
                'tasks as assigned_count' => fn($q) => $isManager ? $q->whereHas('project', fn($pq) => $pq->where('manager_id', $user->id)) : $q,
                'tasks as completed_count' => fn($q) => ($isManager ? $q->whereHas('project', fn($pq) => $pq->where('manager_id', $user->id)) : $q)->where('status', 'completed'),
                'tasks as pending_count'   => fn($q) => ($isManager ? $q->whereHas('project', fn($pq) => $pq->where('manager_id', $user->id)) : $q)->where('status', '!=', 'completed'),
                'tasks as overdue_count'   => fn($q) => ($isManager ? $q->whereHas('project', fn($pq) => $pq->where('manager_id', $user->id)) : $q)->where('status', '!=', 'completed')->where('due_date', '<', $today),
            ])
            ->get()
            ->filter(fn($emp) => !$isManager || $emp->assigned_count > 0)
            ->map(function ($emp) {
                $emp->completion_rate = $emp->assigned_count > 0 
                    ? round(($emp->completed_count / $emp->assigned_count) * 100) 
                    : 0;
                return $emp;
            })
            ->values();

        // 3. Project Completion Percentages
        $projects = (clone $projectQuery)->with('manager')
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
