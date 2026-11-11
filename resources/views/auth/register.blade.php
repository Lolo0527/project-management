@extends('layouts.auth')
@section('title', 'Sign Up - Planova')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="margin: 0 0 6px 0; font-size: 1.8rem; font-weight: 800;">Create your account</h2>
    <p style="margin: 0; color: var(--pico-muted-color); font-size: 0.95rem;">Join Planova to manage projects, tasks and collaborate with your team.</p>
</div>

<form method="POST" action="{{ route('register.submit') }}">
    @csrf

    <!-- Full Name -->
    <label for="name" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px;">
        Full Name
    </label>
    <input type="text" 
           id="name" 
           name="name" 
           value="{{ old('name') }}" 
           placeholder="Jane Doe" 
           required 
           autofocus>
    @error('name') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 10px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Work Email -->
    <label for="email" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Work Email Address
    </label>
    <input type="email" 
           id="email" 
           name="email" 
           value="{{ old('email') }}" 
           placeholder="jane@company.com" 
           required>
    @error('email') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 10px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Department -->
    <label for="department" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Department
    </label>
    <input type="text" 
           id="department" 
           name="department" 
           value="{{ old('department') }}" 
           placeholder="Engineering, Design, Marketing, etc." 
           required>
    @error('department') 
        <small style="color: #dc2626; display: block; margin-top: -10px; margin-bottom: 10px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Password with Toggle -->
    <label for="password" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Password (min. 8 characters)
    </label>
    <div class="password-toggle-wrapper">
        <input type="password" 
               id="password" 
               name="password" 
               placeholder="••••••••" 
               required>
        <button type="button" 
                class="password-toggle-btn" 
                onclick="togglePassword('password', 'pwd-icon-reg')" 
                title="Show or hide password" 
                tabindex="-1">
            <span id="pwd-icon-reg">🙈</span>
        </button>
    </div>
    @error('password') 
        <small style="color: #dc2626; display: block; margin-top: -6px; margin-bottom: 10px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <!-- Confirm Password with Toggle -->
    <label for="password_confirmation" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Confirm Password
    </label>
    <div class="password-toggle-wrapper">
        <input type="password" 
               id="password_confirmation" 
               name="password_confirmation" 
               placeholder="••••••••" 
               required>
        <button type="button" 
                class="password-toggle-btn" 
                onclick="togglePassword('password_confirmation', 'pwd-icon-conf')" 
                title="Show or hide password" 
                tabindex="-1">
            <span id="pwd-icon-conf">🙈</span>
        </button>
    </div>

    <!-- Optional Invitation Code / Token -->
    <label for="invitation_token" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; margin-top: 10px;">
        Invitation Token (Optional)
    </label>
    <input type="text" 
           id="invitation_token" 
           name="invitation_token" 
           value="{{ old('invitation_token', request('token')) }}" 
           placeholder="Paste your invitation token if you have one">

    <!-- Terms and Privacy Acknowledgement -->
    <label style="margin-top: 14px; margin-bottom: 20px; font-size: 0.84rem; display: flex; align-items: flex-start; gap: 8px;">
        <input type="checkbox" name="terms" value="1" required style="margin-top: 3px;">
        <span>I acknowledge the company policy, terms of service and workspace privacy guidelines.</span>
    </label>
    @error('terms') 
        <small style="color: #dc2626; display: block; margin-top: -14px; margin-bottom: 12px; font-weight: 500;">
            {{ $message }}
        </small> 
    @enderror

    <button type="submit" style="width: 100%; padding: 12px; font-weight: 700; font-size: 1rem; border-radius: 6px;">
        Create Employee Account
    </button>
</form>

<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--pico-muted-border-color); text-align: center; font-size: 0.88rem;">
    Already have an active account? 
    <a href="{{ route('login') }}" style="font-weight: 700;">Sign in here →</a>
</div>
@endsection
