@extends('layouts.app')
@section('title', 'Employee Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Employee Management</h2>
    <a href="{{ route('employees.create') }}" role="button">+ Register New Employee</a>
</div>

<article>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
                <tr>
                    <td><strong>{{ $emp->name }}</strong></td>
                    <td>{{ $emp->email }}</td>
                    <td>{{ $emp->department ?? 'General' }}</td>
                    <td><mark>{{ ucfirst($emp->role) }}</mark></td>
                    <td>
                        <span style="color: {{ $emp->is_active ? '#2e7d32' : '#c62828' }}; font-weight: bold;">
                            {{ $emp->is_active ? 'Active' : 'Deactivated' }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 8px;">
                            <a href="{{ route('employees.edit', $emp) }}" role="button" class="outline" style="padding: 4px 10px; font-size: 0.85rem;">Edit</a>
                            
                            @if(auth()->id() !== $emp->id)
                                <form method="POST" action="{{ route('employees.toggle-status', $emp) }}" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $emp->is_active ? 'secondary' : 'contrast' }}" style="padding: 4px 10px; font-size: 0.85rem; margin: 0;">
                                        {{ $emp->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No employees found.</td></tr>
            @endforelse
        </tbody>
    </table>
</article>

<p>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}">← Back to Admin Dashboard</a>
    @elseif(auth()->user()->role === 'manager')
        <a href="{{ route('manager.dashboard') }}">← Back to Manager Dashboard</a>
    @endif
</p>
@endsection
