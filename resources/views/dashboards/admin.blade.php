@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0;">Admin Dashboard</h2>
        <p style="margin: 0;"><small>System overview, user administration, and company performance.</small></p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('employees.create') }}" role="button">+ Register Employee</a>
        <a href="{{ route('employees.index') }}" role="button" class="outline">Manage Users</a>
        <a href="{{ route('kanban.index') }}" role="button" class="secondary">📋 Kanban</a>
        <a href="{{ route('manager.reports.index') }}" role="button" class="secondary">📊 Reports</a>
    </div>
</div>

<!-- System Summary Metrics -->
<div class="grid" style="margin-bottom: 25px;">
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Total Users</small>
        <h3 style="color: #1976d2; margin: 5px 0 0 0;">{{ $totalUsers }}</h3>
    </article>
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Total Projects</small>
        <h3 style="color: #2e7d32; margin: 5px 0 0 0;">{{ $totalProjects }}</h3>
    </article>
    <article style="text-align: center; padding: 15px;">
        <small style="color: gray;">Total Tasks</small>
        <h3 style="color: #f57f17; margin: 5px 0 0 0;">{{ $totalTasks }}</h3>
    </article>
</div>

<!-- Recent Users Table -->
<article>
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Recent User Accounts</h3>
        <a href="{{ route('employees.index') }}">View All →</a>
    </header>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentUsers as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->department ?? 'General' }}</td>
                    <td><mark>{{ ucfirst($user->role) }}</mark></td>
                    <td>
                        <span style="color: {{ $user->is_active ? '#2e7d32' : '#c62828' }}; font-weight: bold;">
                            {{ $user->is_active ? 'Active' : 'Deactivated' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('employees.edit', $user) }}" role="button" class="outline" style="padding: 2px 8px; font-size: 0.8rem;">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</article>
@endsection
