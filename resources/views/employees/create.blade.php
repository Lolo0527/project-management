@extends('layouts.app')
@section('title', 'Register New Employee')

@section('content')
<article style="max-width: 560px; margin: 20px auto;">
    <h2>Register New Employee</h2>
    <p><small>Enter employee details. You can provide an initial password or leave it blank to auto-generate one.</small></p>

    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        <label>Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Jane Doe" required>
        @error('name') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="jane@company.com" required>
        @error('email') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Department</label>
        <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Engineering, Sales, Marketing" required>
        @error('department') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Role</label>
        <select name="role" required>
            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee (View & update assigned tasks)</option>
            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager (Manage projects & assign tasks)</option>
            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Manage users & system settings)</option>
        </select>
        @error('role') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Initial Password (Optional)</label>
        <input type="text" name="password" placeholder="Leave blank to generate random password">
        @error('password') <small style="color: red;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 15px;">Save & Send Credentials</button>
    </form>

    <p><a href="{{ route('employees.index') }}">← Cancel and Return to Employee List</a></p>
</article>
@endsection
