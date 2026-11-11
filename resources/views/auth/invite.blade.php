@extends('layouts.auth')
@section('title', 'Accept Team Invitation - Planova')

@section('content')
<div style="margin-bottom: 20px;">
    <span style="background: rgba(37, 99, 235, 0.12); color: #2563eb; padding: 4px 10px; border-radius: 4px; font-size: 0.82rem; font-weight: 700; display: inline-block; margin-bottom: 10px;">
        ✉️ Official Team Invitation
    </span>
    <h2 style="margin: 0 0 6px 0; font-size: 1.8rem; font-weight: 800;">Welcome to Planova</h2>
    <p style="margin: 0; color: var(--pico-muted-color); font-size: 0.95rem;">
        You have been invited by your administrator to join the team as an <strong>{{ ucfirst($user->role) }}</strong> in <strong>{{ $user->department }}</strong>. Complete your profile to activate your account.
    </p>
</div>

<form method="POST" action="{{ route('register.invite.submit', ['token' => $token]) }}">
    @csrf

    <!-- Work Email (Readonly) -->
    <label for="email" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px;">
        Work Email Address
    </label>
    <input type="email" id="email" value="{{ $user->email }}" readonly disabled style="opacity: 0.85; background: var(--pico-card-background-color);">

    <!-- Full Name -->
    <label for="name" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Full Name
    </label>
    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
    @error('name') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 10px; font-weight: 500;">{{ $message }}</small> 
    @enderror

    <!-- Department -->
    <label for="department" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Department
    </label>
    <input type="text" id="department" name="department" value="{{ old('department', $user->department) }}" required>
    @error('department') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 10px; font-weight: 500;">{{ $message }}</small> 
    @enderror

    <!-- Password with Toggle -->
    <label for="password" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Create Password (min. 8 characters)
    </label>
    <div class="password-toggle-wrapper">
        <input type="password" id="password" name="password" placeholder="••••••••" required>
        <button type="button" class="password-toggle-btn" onclick="togglePassword('password', 'pwd-icon-inv')" title="Show or hide password" tabindex="-1">
            <span id="pwd-icon-inv">🙈</span>
        </button>
    </div>
    @error('password') 
        <small style="color: #dc2626; display: block; margin-top: -6px; margin-bottom: 10px; font-weight: 500;">{{ $message }}</small> 
    @enderror

    <!-- Confirm Password with Toggle -->
    <label for="password_confirmation" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Confirm Password
    </label>
    <div class="password-toggle-wrapper">
        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
        <button type="button" class="password-toggle-btn" onclick="togglePassword('password_confirmation', 'pwd-icon-inv-conf')" title="Show or hide password" tabindex="-1">
            <span id="pwd-icon-inv-conf">🙈</span>
        </button>
    </div>

    <!-- Terms and Privacy Acknowledgement -->
    <label style="margin-top: 14px; margin-bottom: 20px; font-size: 0.84rem; display: flex; align-items: flex-start; gap: 8px;">
        <input type="checkbox" name="terms" value="1" required style="margin-top: 3px;">
        <span>I accept the platform terms of service and workspace privacy policy.</span>
    </label>
    @error('terms') 
        <small style="color: #dc2626; display: block; margin-top: -14px; margin-bottom: 12px; font-weight: 500;">{{ $message }}</small> 
    @enderror

    <button type="submit" style="width: 100%; padding: 12px; font-weight: 700; font-size: 1rem; border-radius: 6px;">
        Activate Account & Access Workspace
    </button>
</form>

<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--pico-muted-border-color); text-align: center; font-size: 0.88rem;">
    Already have login details? 
    <a href="{{ route('login') }}" style="font-weight: 700;">Sign in here →</a>
</div>
@endsection
