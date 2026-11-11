@extends('layouts.app')
@section('title', $project->title . ' - Details')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <a href="{{ route('manager.dashboard') }}">← Back to Manager Dashboard</a>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('manager.projects.edit', $project) }}" role="button" class="outline" style="padding: 4px 12px; font-size: 0.85rem;">✏️ Edit Project</a>
        <a href="{{ route('kanban.index', ['project_id' => $project->id]) }}" role="button" class="secondary" style="padding: 4px 12px; font-size: 0.85rem;">📋 Project Kanban</a>
    </div>
</div>

@if($errors->has('error'))
    <article style="background: #ffebee; color: #c62828; padding: 12px; margin-bottom: 15px;">
        {{ $errors->first('error') }}
    </article>
@endif

<!-- Project Overview -->
<article>
    <header>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0;">{{ $project->title }}</h2>
            <div style="display: flex; gap: 8px; align-items: center;">
                <mark style="background: {{ $project->priority === 'high' ? '#ffcdd2' : ($project->priority === 'medium' ? '#fff9c4' : '#e0e0e0') }};">
                    Priority: {{ ucfirst($project->priority) }}
                </mark>
                <mark style="background: {{ $project->status === 'completed' ? '#c8e6c9' : '#bbdefb' }}; color: {{ $project->status === 'completed' ? '#1b5e20' : '#0d47a1' }};">
                    Status: {{ ucfirst($project->status) }}
                </mark>
            </div>
        </div>
    </header>

    <p>{{ $project->description ?? 'No description provided.' }}</p>

    <!-- Key Metadata -->
    <div class="grid" style="margin-top: 15px;">
        <div><strong>Project Manager:</strong> {{ $project->manager->name ?? 'Unassigned' }}</div>
        <div><strong>Start Date:</strong> {{ $project->start_date ?? 'Not set' }}</div>
        <div>
            <strong>Deadline:</strong> {{ $project->deadline }}
            <form method="POST" action="{{ route('manager.projects.update-deadline', $project) }}" style="display: inline-flex; gap: 6px; margin-top: 4px;">
                @csrf
                @method('PATCH')
                <input type="date" name="deadline" value="{{ $project->deadline }}" style="font-size: 0.8rem; padding: 2px 6px; width: auto; margin: 0;" required>
                <button type="submit" class="outline" style="font-size: 0.8rem; padding: 2px 8px; margin: 0;">Update</button>
            </form>
        </div>
    </div>

    <!-- Assigned Team Members -->
    <div style="margin-top: 20px;">
        <strong>Assigned Team Members ({{ $project->members->count() }}):</strong>
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
            @forelse($project->members as $member)
                <span style="background: var(--pico-card-background-color); border: 1px solid var(--pico-muted-border-color); padding: 4px 10px; border-radius: 4px; font-size: 0.85rem;">
                    👤 {{ $member->name }} <small>({{ $member->department ?? 'General' }})</small>
                </span>
            @empty
                <em>No team members assigned yet.</em>
            @endforelse
        </div>
    </div>

    <!-- Project Completion & Review Status Bar -->
    <hr>
    @php
        $totalTasks = $project->tasks->count();
        $approvedTasks = $project->tasks->where('is_approved', true)->count();
        $canClose = $project->allTasksApproved();
    @endphp
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 10px; background: var(--panel-muted-bg); border-radius: 6px; border: 1px solid var(--panel-muted-border);">
        <div>
            <strong>Project Workflow & Completion:</strong><br>
            <small>
                Approved Tasks: <strong>{{ $approvedTasks }} / {{ $totalTasks }}</strong>
                @if($totalTasks > 0)
                    ({{ round(($approvedTasks / $totalTasks) * 100) }}%)
                @endif
            </small>
        </div>
        <div>
            @if($project->status === 'completed')
                <span style="color: #2e7d32; font-weight: bold; font-size: 1rem;">🏆 Project is Closed & Completed</span>
            @elseif($canClose)
                <form method="POST" action="{{ route('manager.projects.close', $project) }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: #2e7d32; border-color: #2e7d32; margin: 0; padding: 6px 16px;">
                        ✓ Close & Complete Project
                    </button>
                </form>
            @else
                <button disabled style="margin: 0; padding: 6px 14px; opacity: 0.6; cursor: not-allowed;">
                    Close Project ({{ $totalTasks - $approvedTasks }} pending approval)
                </button>
            @endif
        </div>
    </div>
</article>

<!-- Project Tasks -->
<article>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0;">Project Tasks ({{ $project->tasks->count() }})</h3>
        <a href="{{ route('manager.tasks.create', $project) }}" role="button" style="padding: 6px 14px; font-size: 0.9rem;">+ Add Task</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Approval</th>
                <th>Attachment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($project->tasks as $task)
                <tr>
                    <td>
                        <strong><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></strong>
                        @if($task->description)
                            <br><small style="color: gray;">{{ $task->description }}</small>
                        @endif
                    </td>
                    <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
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
                        @if($task->is_approved)
                            <span style="color: #2e7d32; font-weight: bold; font-size: 0.85rem;">✓ Approved</span>
                        @elseif($task->status === 'completed')
                            <span style="color: #ef6c00; font-weight: bold; font-size: 0.85rem;">⏳ In Review</span>
                        @else
                            <small style="color: gray;">Pending</small>
                        @endif
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
                <tr><td colspan="7">No tasks added to this project yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>
@endsection
