@extends('layouts.app')
@section('title', 'Productivity Reports')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0;">📊 Productivity & Progress Reports</h2>
        <p style="margin: 0;"><small>Monitor employee workload, task turnaround, and project completion milestones.</small></p>
    </div>
    <a href="{{ route('manager.dashboard') }}" role="button" class="outline">← Back to Dashboard</a>
</div>

<!-- 1. High-Level Summary Cards -->
<div class="grid" style="margin-bottom: 25px;">
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Completed Tasks</small>
        <h3 style="color: #2e7d32; margin: 5px 0 0 0;">{{ $totalCompletedTasks }}</h3>
    </article>
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Pending Tasks</small>
        <h3 style="color: #f57f17; margin: 5px 0 0 0;">{{ $totalPendingTasks }}</h3>
    </article>
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Overdue Tasks</small>
        <h3 style="color: #c62828; margin: 5px 0 0 0;">{{ $totalOverdueTasks }}</h3>
    </article>
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Projects Completed</small>
        <h3 style="color: #1976d2; margin: 5px 0 0 0;">{{ $completedProjects }} <span style="font-size: 1rem; color: gray;">/ {{ $totalProjects }}</span></h3>
    </article>
</div>

<!-- 2. Employee Workload & Productivity Table -->
<article style="margin-bottom: 30px;">
    <header>
        <h3 style="margin: 0;">👥 Employee Workload & Task Performance</h3>
    </header>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th style="text-align: center;">Assigned</th>
                <th style="text-align: center;">Completed</th>
                <th style="text-align: center;">Pending</th>
                <th style="text-align: center;">Overdue</th>
                <th>Completion Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
                <tr>
                    <td><strong>{{ $emp->name }}</strong></td>
                    <td>{{ $emp->department ?? 'General' }}</td>
                    <td style="text-align: center;"><strong>{{ $emp->assigned_count }}</strong></td>
                    <td style="text-align: center; color: #2e7d32; font-weight: bold;">{{ $emp->completed_count }}</td>
                    <td style="text-align: center; color: #f57f17;">{{ $emp->pending_count }}</td>
                    <td style="text-align: center; color: {{ $emp->overdue_count > 0 ? '#c62828' : 'gray' }}; font-weight: {{ $emp->overdue_count > 0 ? 'bold' : 'normal' }};">
                        {{ $emp->overdue_count }}
                    </td>
                    <td style="min-width: 140px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <progress value="{{ $emp->completion_rate }}" max="100" style="margin: 0; height: 10px;"></progress>
                            <small style="font-weight: bold; width: 35px;">{{ $emp->completion_rate }}%</small>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No employees found.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>

<!-- 3. Project Completion Percentages Table -->
<article>
    <header>
        <h3 style="margin: 0;">📁 Project Completion Percentages</h3>
    </header>
    <table>
        <thead>
            <tr>
                <th>Project</th>
                <th>Manager</th>
                <th>Status</th>
                <th style="text-align: center;">Tasks (Done / Total)</th>
                <th>Completion %</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $p)
                <tr>
                    <td><strong>{{ $p->title }}</strong></td>
                    <td>{{ $p->manager->name ?? 'Unassigned' }}</td>
                    <td>
                        <mark style="background: {{ $p->status === 'completed' ? '#c8e6c9' : '#bbdefb' }};">
                            {{ ucfirst($p->status) }}
                        </mark>
                    </td>
                    <td style="text-align: center;">
                        <strong>{{ $p->completed_tasks_count }}</strong> / {{ $p->tasks_count }}
                    </td>
                    <td style="min-width: 160px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <progress value="{{ $p->completion_percentage }}" max="100" style="margin: 0; height: 10px;"></progress>
                            <small style="font-weight: bold; width: 35px;">{{ $p->completion_percentage }}%</small>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('manager.projects.show', $p) }}">View Details →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No projects found.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>
@endsection
