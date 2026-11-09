@extends('layouts.app')
@section('title', 'Manager Dashboard')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin-bottom: 5px;">Manager Dashboard</h2>
        <p style="margin: 0;">Overview of company projects, workload, and team performance.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('kanban.index') }}" role="button">📋 Kanban Board</a>
        <a href="{{ route('manager.reports.index') }}" role="button" class="secondary">📊 Reports</a>
        <a href="{{ route('employees.index') }}" role="button" class="outline">Manage Employees</a>
    </div>
</div>

<!-- 1. Key Statistics Cards -->
<div class="grid">
    <article style="text-align: center;">
        <small>Total Employees</small>
        <h3>{{ $totalEmployees }}</h3>
    </article>
    <article style="text-align: center;">
        <small>Total Projects</small>
        <h3>{{ $totalProjects }}</h3>
    </article>
    <article style="text-align: center;">
        <small>Active Projects</small>
        <h3 style="color: #2e7d32;">{{ $activeProjects }}</h3>
    </article>
    <article style="text-align: center;">
        <small>Completed Projects</small>
        <h3>{{ $completedProjects }}</h3>
    </article>
    <article style="text-align: center;">
        <small>Pending Tasks</small>
        <h3 style="color: #ef6c00;">{{ $pendingTasks }}</h3>
    </article>
    <article style="text-align: center;">
        <small>Overdue Tasks</small>
        <h3 style="color: #c62828;">{{ $overdueTasks }}</h3>
    </article>
</div>

<!-- 2. Projects List (Clickable for Details) -->
<article>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0;">Company Projects</h3>
        <a href="{{ route('manager.projects.create') }}" role="button" style="padding: 6px 14px; font-size: 0.9rem;">+ Create Project</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Status</th>
                <th>Deadline</th>
                <th>Tasks</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $p)
                <tr>
                    <td><strong>{{ $p->title }}</strong></td>
                    <td>
                        <mark style="{{ $p->status === 'completed' ? 'background: #c8e6c9;' : 'background: #bbdefb;' }}">
                            {{ ucfirst($p->status) }}
                        </mark>
                    </td>
                    <td>{{ $p->deadline }}</td>
                    <td>{{ $p->tasks_count }} task(s)</td>
                    <td>
                        <a href="{{ route('manager.projects.show', $p) }}">View Details →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No projects found.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>

<!-- 3. Upcoming Deadlines & Employee Productivity -->
<div class="grid">
    <!-- Upcoming Deadlines -->
    <article>
        <h3>Upcoming Deadlines</h3>
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Project</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($upcomingDeadlines as $t)
                    <tr>
                        <td>{{ $t->title }}</td>
                        <td><small>{{ $t->project->title ?? 'N/A' }}</small></td>
                        <td><strong>{{ $t->due_date }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="3">No upcoming deadlines.</td></tr>
                @endforelse
            </tbody>
        </table>
    </article>

    <!-- Employee Productivity -->
    <article>
        <h3>Employee Productivity</h3>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Completed Tasks</th>
                    <th>Total Assigned</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employeeStats as $emp)
                    <tr>
                        <td>{{ $emp->name }}</td>
                        <td><strong>{{ $emp->completed_tasks_count }}</strong></td>
                        <td>{{ $emp->tasks_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </article>
</div>
@endsection
