@extends('layouts.app')
@section('title', 'Edit Employee - ' . $employee->name)

@section('content')
<article style="max-width: 560px; margin: 20px auto;">
    <h2>Edit Employee Account</h2>
    <p><small>Update details for <strong>{{ $employee->name }}</strong>.</small></p>

    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf
        @method('PUT')

        <label>Full Name</label>
        <input type="text" name="name" value="{{ old('name', $employee->name) }}" required>
        @error('name') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Email Address</label>
        <input type="email" name="email" value="{{ old('email', $employee->email) }}" required>
        @error('email') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Department</label>
        <input type="text" name="department" value="{{ old('department', $employee->department) }}" required>
        @error('department') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Role</label>
        <select name="role" required>
            <option value="employee" {{ old('role', $employee->role) === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager" {{ old('role', $employee->role) === 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ old('role', $employee->role) === 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        @error('role') <small style="color: red;">{{ $message }}</small> @enderror

        <label>New Password (Optional)</label>
        <input type="password" name="password" placeholder="Leave blank to keep existing password">
        @error('password') <small style="color: red;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 15px;">Update Employee Information</button>
    </form>

    <p><a href="{{ route('employees.index') }}">← Cancel and Return to Employee List</a></p>
</article>
@endsection
