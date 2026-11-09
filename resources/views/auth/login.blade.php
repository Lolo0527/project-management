@extends('layouts.app')
@section('title', 'Login')

@section('content')
<article style="max-width: 420px; margin: 40px auto;">
    <h2>Sign In</h2>
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <label>Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email') <small style="color: red;">{{ $message }}</small> @enderror

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" style="margin-top: 15px;">Login</button>
    </form>
</article>
@endsection
