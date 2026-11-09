@extends('layouts.app')
@section('title', 'Employee Dashboard')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin-bottom: 5px;">Employee Dashboard</h2>
        <p style="margin: 0;">Welcome back, <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->department ?? 'General' }})</p>
    </div>
    <a href="{{ route('kanban.index') }}" role="button">📋 Kanban Board</a>
</div>

<!-- 1. Recent Notifications -->
@if($notifications->count() > 0)
    <article style="background: #e8f0fe; border-left: 4px solid #1a73e8; margin-bottom: 25px;">
        <h4 style="margin-bottom: 10px;">🔔 Recent Notifications</h4>
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($notifications as $notif)
                <li style="margin-bottom: 6px;">
                    {{ $notif->data['message'] ?? 'New notification' }}
                    <small style="color: gray;">({{ $notif->created_at->diffForHumans() }})</small>
                </li>
            @endforeach
        </ul>
    </article>
@endif

<!-- 2. My Assigned Tasks -->
<article>
    <h3>My Assigned Tasks ({{ $tasks->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th>Project</th>
                <th>Priority</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Attachment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td>
                        <strong><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></strong>
                        @if($task->description)
                            <br><small style="color: gray;">{{ $task->description }}</small>
                        @endif
                    </td>
                    <td>{{ $task->project->title ?? 'N/A' }}</td>
                    <td>
                        <mark style="background: {{ $task->priority === 'high' ? '#ffcdd2' : ($task->priority === 'medium' ? '#fff9c4' : '#e0e0e0') }};">
                            {{ ucfirst($task->priority ?? 'medium') }}
                        </mark>
                    </td>
                    <td>
                        <small>
                            Start: {{ $task->start_date ?? 'N/A' }}<br>
                            Due: <strong>{{ $task->due_date }}</strong>
                        </small>
                    </td>
                    <td>
                        <mark style="{{ $task->status === 'completed' ? 'background: #c8e6c9;' : 'background: #bbdefb;' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </mark>
                    </td>
                    <td>
                        @if($task->attachment_path)
                            <a href="{{ asset('storage/' . $task->attachment_path) }}" target="_blank" role="button" class="outline" style="padding: 2px 8px; font-size: 0.8rem;">📎 View</a>
                        @else
                            <small>None</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No tasks assigned to you currently.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>

<!-- 3. My Assigned Projects -->
<article>
    <h3>My Assigned Projects ({{ $projects->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Manager</th>
                <th>Priority</th>
                <th>Deadline</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $proj)
                <tr>
                    <td><strong>{{ $proj->title }}</strong></td>
                    <td>{{ $proj->manager->name ?? 'Unassigned' }}</td>
                    <td>
                        <mark style="background: {{ $proj->priority === 'high' ? '#ffcdd2' : ($proj->priority === 'medium' ? '#fff9c4' : '#e0e0e0') }};">
                            {{ ucfirst($proj->priority ?? 'medium') }}
                        </mark>
                    </td>
                    <td><strong>{{ $proj->deadline }}</strong></td>
                    <td>
                        <mark style="{{ $proj->status === 'completed' ? 'background: #c8e6c9;' : 'background: #bbdefb;' }}">
                            {{ ucfirst($proj->status) }}
                        </mark>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">You are not currently assigned to any projects.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>
@endsection
