@extends('layouts.auth')
@section('title', 'Sign In - Planova')

@section('content')
<div style="margin-bottom: 25px;">
    <h2 style="margin: 0 0 6px 0; font-size: 1.8rem; font-weight: 800;">Welcome back</h2>
    <p style="margin: 0; color: var(--pico-muted-color); font-size: 0.95rem;">Please enter your credentials to access your workspace.</p>
</div>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Work Email -->
    <label for="email" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px;">
        Work Email Address
    </label>
    <input type="email" 
           id="email" 
           name="email" 
           value="{{ old('email') }}" 
           placeholder="name@company.com" 
           required 
           autofocus 
           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
    @error('email') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 12px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Password with Show/Hide Toggle -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; margin-bottom: 4px;">
        <label for="password" style="font-weight: 600; font-size: 0.88rem; margin: 0;">
            Password
        </label>
        <a href="{{ route('password.request') }}" style="font-size: 0.82rem; font-weight: 600; text-decoration: none;">
            Forgot password?
        </a>
    </div>

    <div class="password-toggle-wrapper">
        <input type="password" 
               id="password" 
               name="password" 
               placeholder="••••••••" 
               required 
               aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
        <button type="button" 
                class="password-toggle-btn" 
                onclick="togglePassword('password', 'password-icon')" 
                title="Show or hide password" 
                tabindex="-1">
            <span id="password-icon">🙈</span>
        </button>
    </div>
    @error('password') 
        <small style="color: #dc2626; display: block; margin-top: -6px; margin-bottom: 12px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Remember Me Option -->
    <label style="margin-top: 16px; margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="margin: 0;">
        Remember my sign-in on this device
    </label>

    <!-- Sign In Button -->
    <button type="submit" style="width: 100%; padding: 12px; font-weight: 700; font-size: 1rem; border-radius: 6px;">
        Sign In to Planova
    </button>
</form>

<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--pico-muted-border-color); text-align: center; font-size: 0.88rem;">
    Have an invitation or need an account? 
    <a href="{{ route('register') }}" style="font-weight: 700;">Join your team →</a>
</div>
@endsection
