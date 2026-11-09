@extends('layouts.app')
@section('title', 'Assign New Task - ' . $project->title)

@section('content')
<article style="max-width: 650px; margin: 20px auto;">
    <h2>Assign New Task</h2>
    <p><small>Project: <strong>{{ $project->title }}</strong></small></p>

    <form method="POST" action="{{ route('manager.tasks.store', $project) }}" enctype="multipart/form-data">
        @csrf

        <!-- Task Title -->
        <label>Task Title</label>
        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Design homepage" required>
        @error('title') <small style="color: red;">{{ $message }}</small> @enderror

        <!-- Description -->
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Provide task instructions and deliverables...">{{ old('description') }}</textarea>
        @error('description') <small style="color: red;">{{ $message }}</small> @enderror

        <!-- Assignee & Priority Grid -->
        <div class="grid">
            <div>
                <label>Assigned Employee</label>
                <select name="assigned_to" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->department ?? 'General' }})
                        </option>
                    @endforeach
                </select>
                @error('assigned_to') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Priority</label>
                <select name="priority" required>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                </select>
                @error('priority') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
        </div>

        <!-- Dates Grid -->
        <div class="grid">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}">
                @error('start_date') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" required>
                @error('due_date') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
        </div>

        <!-- Status & Attachment Grid -->
        <div class="grid">
            <div>
                <label>Status</label>
                <select name="status" required>
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                @error('status') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Attachment (Optional)</label>
                <input type="file" name="attachment">
                @error('attachment') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
        </div>

        <button type="submit" style="margin-top: 15px;">Create & Assign Task</button>
    </form>

    <p><a href="{{ route('manager.projects.show', $project) }}">← Cancel and Return to Project</a></p>
</article>
@endsection
