<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    // Display the signup / registration page
    public function showRegistrationForm(Request $request)
    {
        $token = $request->query('token');

        if ($token) {
            return redirect()->route('register.invite', ['token' => $token]);
        }

        return view('auth.register');
    }

    // Display registration page for a specific secure invitation token
    public function showInviteForm(string $token)
    {
        $user = User::where('invitation_token', $token)->first();

        if (!$user || !$user->hasValidInvitation()) {
            return redirect()->route('login')->withErrors([
                'email' => 'This invitation link is invalid or has expired. Please contact your administrator for a new invite.',
            ]);
        }

        return view('auth.invite', compact('user', 'token'));
    }

    // Complete registration via invitation token
    public function registerWithInvite(Request $request, string $token)
    {
        $user = User::where('invitation_token', $token)->first();

        if (!$user || !$user->hasValidInvitation()) {
            return redirect()->route('login')->withErrors([
                'email' => 'This invitation link is invalid or has expired. Please contact your administrator.',
            ]);
        }

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'password'   => 'required|string|min:8|confirmed',
            'terms'      => 'accepted',
        ]);

        $user->update([
            'name'                  => $data['name'],
            'department'            => $data['department'],
            'password'              => Hash::make($data['password']),
            'is_active'             => true,
            'invitation_token'      => null,
            'invitation_expires_at' => null,
        ]);

        Auth::login($user);

        return match ($user->role) {
            'admin'    => redirect()->route('admin.dashboard')->with('success', 'Account activated! Welcome to Planova.'),
            'manager'  => redirect()->route('manager.dashboard')->with('success', 'Account activated! Welcome to Planova.'),
            default    => redirect()->route('employee.dashboard')->with('success', 'Account activated! Welcome to Planova.'),
        };
    }

    // Public self-registration (defaults to Employee, requires admin approval)
    public function register(Request $request)
    {
        if ($request->filled('invitation_token')) {
            return $this->registerWithInvite($request, $request->input('invitation_token'));
        }

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'department' => 'required|string|max:255',
            'password'   => 'required|string|min:8|confirmed',
            'terms'      => 'accepted',
        ]);

        // Security: Self-registered accounts ALWAYS default to employee and are inactive until approved
        User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'department' => $data['department'],
            'role'       => 'employee',
            'password'   => Hash::make($data['password']),
            'is_active'  => false,
        ]);

        return redirect()->route('login')->with('success', 'Registration submitted! Your employee account is pending administrator approval before you can log in.');
    }
}
