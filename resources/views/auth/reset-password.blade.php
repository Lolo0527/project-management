@extends('layouts.auth')
@section('title', 'Set New Password - Planova')

@section('content')
<div style="margin-bottom: 25px;">
    <h2 style="margin: 0 0 6px 0; font-size: 1.8rem; font-weight: 800;">Set a new password</h2>
    <p style="margin: 0; color: var(--pico-muted-color); font-size: 0.95rem;">
        Create a secure new password for your Planova account.
    </p>
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <!-- Email Address -->
    <label for="email" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px;">
        Work Email Address
    </label>
    <input type="email" 
           id="email" 
           name="email" 
           value="{{ old('email', $email) }}" 
           required>
    @error('email') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 12px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- New Password -->
    <label for="password" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        New Password (min. 8 characters)
    </label>
    <div class="password-toggle-wrapper">
        <input type="password" 
               id="password" 
               name="password" 
               placeholder="••••••••" 
               required>
        <button type="button" 
                class="password-toggle-btn" 
                onclick="togglePassword('password', 'pwd-icon-reset')" 
                title="Show or hide password" 
                tabindex="-1">
            <span id="pwd-icon-reset">🙈</span>
        </button>
    </div>
    @error('password') 
        <small style="color: #dc2626; display: block; margin-top: -6px; margin-bottom: 12px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Confirm Password -->
    <label for="password_confirmation" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Confirm New Password
    </label>
    <div class="password-toggle-wrapper">
        <input type="password" 
               id="password_confirmation" 
               name="password_confirmation" 
               placeholder="••••••••" 
               required>
        <button type="button" 
                class="password-toggle-btn" 
                onclick="togglePassword('password_confirmation', 'pwd-icon-reset-conf')" 
                title="Show or hide password" 
                tabindex="-1">
            <span id="pwd-icon-reset-conf">🙈</span>
        </button>
    </div>

    <button type="submit" style="width: 100%; padding: 12px; font-weight: 700; font-size: 1rem; border-radius: 6px; margin-top: 18px;">
        Reset Password & Access Workspace
    </button>
</form>

<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--pico-muted-border-color); text-align: center; font-size: 0.88rem;">
    Return to <a href="{{ route('login') }}" style="font-weight: 700;">Sign in →</a>
</div>
@endsection
