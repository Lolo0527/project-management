@extends('layouts.app')
@section('title', 'Invite / Register Employee - Planova')

@section('content')
<article style="max-width: 560px; margin: 20px auto;">
    <h2>Invite / Register Employee</h2>
    <p><small>Enter employee details. A secure, time-limited registration link will be generated for the employee to set their own password and activate their account.</small></p>

    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        
        <label>Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Jane Doe" required>
        @error('name') <small style="color: #dc2626;">{{ $message }}</small> @enderror

        <label>Work Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="jane@company.com" required>
        @error('email') <small style="color: #dc2626;">{{ $message }}</small> @enderror

        <label>Department</label>
        <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Engineering, Sales, Marketing" required>
        @error('department') <small style="color: #dc2626;">{{ $message }}</small> @enderror

        <label>Role</label>
        <select name="role" required>
            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee (View & update assigned tasks)</option>
            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager (Manage projects & assign tasks)</option>
            @if(auth()->user()->role === 'admin')
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Manage users & system settings)</option>
            @endif
        </select>
        @error('role') <small style="color: #dc2626;">{{ $message }}</small> @enderror

        <label>Initial Password (Optional)</label>
        <input type="password" name="password" placeholder="Leave blank to use invitation-based signup (Recommended)">
        <small style="color: var(--pico-muted-color); display: block; margin-top: -6px; margin-bottom: 15px;">
            Leaving blank generates a secure 3-day invitation link where the employee creates their password.
        </small>
        @error('password') <small style="color: #dc2626;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 10px;">Create Account & Generate Invitation</button>
    </form>

    <p><a href="{{ route('employees.index') }}">← Cancel and Return to Employee List</a></p>
</article>
@endsection
