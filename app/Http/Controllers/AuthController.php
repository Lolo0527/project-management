<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process login and redirect based on role
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // Check if account is active
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact an administrator.']);
            }

            $request->session()->regenerate();

            return match (Auth::user()->role) {
                'admin'    => redirect()->route('admin.dashboard'),
                'manager'  => redirect()->route('manager.dashboard'),
                'employee' => redirect()->route('employee.dashboard'),
                default    => abort(403, 'Unknown user role'),
            };
        }

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    // Logout the current user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
