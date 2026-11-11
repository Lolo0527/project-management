@extends('layouts.auth')
@section('title', 'Forgot Password - Planova')

@section('content')
<div style="margin-bottom: 25px;">
    <h2 style="margin: 0 0 6px 0; font-size: 1.8rem; font-weight: 800;">Reset your password</h2>
    <p style="margin: 0; color: var(--pico-muted-color); font-size: 0.95rem;">
        Enter your work email address below and we'll generate instructions to reset your account password.
    </p>
</div>

@if(session('reset_url'))
    <article style="background: rgba(37, 99, 235, 0.1); border: 1px solid #2563eb; padding: 14px; border-radius: 6px; margin-bottom: 20px;">
        <strong style="color: #2563eb; display: block; margin-bottom: 6px;">🔐 Password Reset Generated:</strong>
        <p style="font-size: 0.85rem; word-break: break-all; margin: 0 0 10px 0;">{{ session('reset_url') }}</p>
        <a href="{{ session('reset_url') }}" role="button" style="padding: 6px 14px; font-size: 0.85rem; display: inline-block;">
            Open Reset Link →
        </a>
    </article>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <label for="email" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px;">
        Work Email Address
    </label>
    <input type="email" 
           id="email" 
           name="email" 
           value="{{ old('email') }}" 
           placeholder="name@company.com" 
           required 
           autofocus>
    @error('email') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 12px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <button type="submit" style="width: 100%; padding: 12px; font-weight: 700; font-size: 1rem; border-radius: 6px; margin-top: 10px;">
        Generate Reset Link
    </button>
</form>

<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--pico-muted-border-color); text-align: center; font-size: 0.88rem;">
    Remembered your password? 
    <a href="{{ route('login') }}" style="font-weight: 700;">Back to login →</a>
</div>
@endsection
