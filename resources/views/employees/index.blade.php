@extends('layouts.app')
@section('title', 'Employee Management - Planova')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0;">Employee Management</h2>
        <p style="margin: 0; color: var(--pico-muted-color); font-size: 0.9rem;">Manage employee accounts, assign departments, and generate secure registration invitations.</p>
    </div>
    <a href="{{ route('employees.create') }}" role="button">+ Invite / Register Employee</a>
</div>

@if(session('invite_url'))
    <article style="background: rgba(37, 99, 235, 0.1); border: 1px solid #2563eb; padding: 14px; border-radius: 6px; margin-bottom: 20px;">
        <strong style="color: #2563eb; display: block; margin-bottom: 4px;">✉️ Secure Registration Invitation Link:</strong>
        <p style="font-size: 0.85rem; word-break: break-all; margin: 0 0 10px 0;">{{ session('invite_url') }}</p>
        <button type="button" onclick="navigator.clipboard.writeText('{{ session('invite_url') }}'); alert('Invitation link copied to clipboard!');" style="padding: 4px 12px; font-size: 0.82rem; width: auto; margin: 0;">
            📋 Copy Invitation Link
        </button>
    </article>
@endif

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
                        @if($emp->invitation_token && !$emp->is_active)
                            <mark style="background: rgba(249, 115, 22, 0.2); color: #c2410c; font-size: 0.8rem;">
                                ⏳ Invite Pending
                            </mark>
                        @elseif($emp->is_active)
                            <span style="color: #15803d; font-weight: bold;">Active</span>
                        @else
                            <span style="color: #b91c1c; font-weight: bold;">Deactivated</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
                            @if($emp->invitation_token && !$emp->is_active)
                                <form method="POST" action="{{ route('employees.resend-invite', $emp) }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="outline" style="padding: 4px 8px; font-size: 0.8rem; margin: 0;" title="Regenerate invitation link">
                                        ✉️ Re-Invite
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('employees.edit', $emp) }}" role="button" class="outline" style="padding: 4px 10px; font-size: 0.82rem; margin: 0;">Edit</a>
                            
                            @if(auth()->id() !== $emp->id && ($emp->role !== 'admin' || auth()->user()->role === 'admin'))
                                <form method="POST" action="{{ route('employees.toggle-status', $emp) }}" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $emp->is_active ? 'secondary' : 'contrast' }}" style="padding: 4px 10px; font-size: 0.82rem; margin: 0;">
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
