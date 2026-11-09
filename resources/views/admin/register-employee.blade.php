@extends('layouts.app')
@section('title', 'Register Employee')

@section('content')
<article style="max-width: 500px; margin: 20px auto;">
    <h2>Register New Employee</h2>
    <form method="POST" action="{{ route('admin.register.store') }}">
        @csrf
        <label>Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
        @error('name') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        @error('email') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Password</label>
        <input type="password" name="password" required>
        @error('password') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Role</label>
        <select name="role" required>
            <option value="employee">Employee</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
        </select>
        @error('role') <small style="color: red;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 15px;">Register User</button>
    </form>
    <p><a href="{{ route('admin.dashboard') }}">← Back to Dashboard</a></p>
</article>
@endsection
