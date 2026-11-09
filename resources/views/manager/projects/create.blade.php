@extends('layouts.app')
@section('title', 'Create New Project')

@section('content')
<article style="max-width: 650px; margin: 20px auto;">
    <h2>Create New Project</h2>
    <p><small>Fill out the details below to initiate a new company project and assign team members.</small></p>

    <form method="POST" action="{{ route('manager.projects.store') }}">
        @csrf

        <!-- Project Name -->
        <label>Project Name</label>
        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Website Redesign" required>
        @error('title') <small style="color: red;">{{ $message }}</small> @enderror

        <!-- Description -->
        <label>Project Description</label>
        <textarea name="description" rows="3" placeholder="Describe the project goals and scope...">{{ old('description') }}</textarea>
        @error('description') <small style="color: red;">{{ $message }}</small> @enderror

        <!-- Dates Grid -->
        <div class="grid">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                @error('start_date') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Deadline</label>
                <input type="date" name="deadline" value="{{ old('deadline') }}" required>
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
                        <option value="{{ $m->id }}" {{ old('manager_id', auth()->id()) == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} ({{ ucfirst($m->role) }})
                        </option>
                    @endforeach
                </select>
                @error('manager_id') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
            <div>
                <label>Project Priority</label>
                <select name="priority" required>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                </select>
                @error('priority') <small style="color: red;">{{ $message }}</small> @enderror
            </div>
        </div>

        <!-- Team Members Selection -->
        <label style="margin-top: 15px;"><strong>Assign Team Members</strong></label>
        <div style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px solid var(--pico-muted-border-color); border-radius: 6px; margin-bottom: 20px;">
            @forelse($employees as $emp)
                <label style="margin-bottom: 8px;">
                    <input type="checkbox" name="team_members[]" value="{{ $emp->id }}" {{ in_array($emp->id, old('team_members', [])) ? 'checked' : '' }}>
                    {{ $emp->name }} <small>({{ $emp->department ?? 'General' }})</small>
                </label>
            @empty
                <p><small>No employees available to assign.</small></p>
            @endforelse
        </div>
        @error('team_members') <small style="color: red;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 10px;">Save Project</button>
    </form>

    <p><a href="{{ route('manager.dashboard') }}">← Cancel and Return to Dashboard</a></p>
</article>
@endsection
