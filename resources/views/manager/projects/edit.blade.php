@extends('layouts.app')
@section('title', 'Edit Project - ' . $project->title)

@section('content')
<article style="max-width: 650px; margin: 20px auto;">
    <h2>Edit Project</h2>
    <p><small>Update project details, deadlines, assigned manager, priority, and team members.</small></p>

    <form method="POST" action="{{ route('manager.projects.update', $project) }}">
        @csrf
        @method('PUT')

        <!-- Project Name -->
        <label>Project Name</label>
        <input type="text" name="title" value="{{ old('title', $project->title) }}" placeholder="e.g. Website Redesign" required>
        @error('title') <small style="color: red;">{{ $message }}</small> @enderror

        <!-- Description -->
        <label>Project Description</label>
        <textarea name="description" rows="3" placeholder="Describe the project goals and scope...">{{ old('description', $project->description) }}</textarea>
        @error('description') <small style="color: red;">{{ $message }}</small> @enderror

        <!-- Dates Grid -->
        <div class="grid">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', $project->start_date) }}" required>
                @error('start_date') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Deadline</label>
                <input type="date" name="deadline" value="{{ old('deadline', $project->deadline) }}" required>
                @error('deadline') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
        </div>

        <!-- Manager & Priority Grid -->
        <div class="grid">
            <div>
                <label>Project Manager</label>
                <select name="manager_id" required>
                    <option value="">Select Manager</option>
                    @foreach($managers as $m)
                        <option value="{{ $m->id }}" {{ old('manager_id', $project->manager_id) == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} ({{ ucfirst($m->role) }})
                        </option>
                    @endforeach
                </select>
                @error('manager_id') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Project Priority</label>
                <select name="priority" required>
                    <option value="low" {{ old('priority', $project->priority) === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', $project->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority', $project->priority) === 'high' ? 'selected' : '' }}>High</option>
                </select>
                @error('priority') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
        </div>

        <!-- Project Status -->
        <div style="margin-top: 15px;">
            <label>Project Status</label>
            <select name="status" required>
                <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            @error('status') <small style="color: red;">{{ $message }}</small> @enderror
        </div>

        <!-- Team Members Selection -->
        @php
            $currentMemberIds = old('team_members', $project->members->pluck('id')->toArray());
        @endphp
        <label style="margin-top: 15px;"><strong>Assign Team Members</strong></label>
        <div style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px solid var(--pico-muted-border-color); border-radius: 6px; margin-bottom: 20px;">
            @forelse($employees as $emp)
                <label style="margin-bottom: 8px;">
                    <input type="checkbox" name="team_members[]" value="{{ $emp->id }}" {{ in_array($emp->id, $currentMemberIds) ? 'checked' : '' }}>
                    {{ $emp->name }} <small>({{ $emp->department ?? 'General' }})</small>
                </label>
            @empty
                <p><small>No employees available to assign.</small></p>
            @endforelse
        </div>
        @error('team_members') <small style="color: red;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 10px;">Update Project</button>
    </form>

    <p><a href="{{ route('manager.projects.show', $project) }}">← Cancel and Return to Project</a></p>
</article>
@endsection
