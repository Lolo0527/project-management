<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeManagementController extends Controller
{
    // List all employees
    public function index()
    {
        $employees = User::orderBy('id', 'desc')->get();

        return view('employees.index', compact('employees'));
    }

    // Show registration form
    public function create()
    {
        return view('employees.create');
    }

    // Register a new employee & generate secure invitation
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'department' => 'required|string|max:255',
            'role'       => 'required|in:admin,manager,employee',
            'password'   => 'nullable|string|min:8',
        ]);

        // Non-admin users cannot create an administrator account
        if ($data['role'] === 'admin' && $currentUser->role !== 'admin') {
            abort(403, 'Only administrators can create admin accounts.');
        }

        $hasManualPassword = !empty($data['password']);
        $invitationToken = $hasManualPassword ? null : Str::random(40);
        $expiresAt = $hasManualPassword ? null : now()->addDays(3);

        $employee = User::create([
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'department'            => $data['department'],
            'role'                  => $data['role'],
            'password'              => Hash::make($hasManualPassword ? $data['password'] : Str::random(32)),
            'is_active'             => $hasManualPassword,
            'invitation_token'      => $invitationToken,
            'invitation_expires_at' => $expiresAt,
        ]);

        if ($invitationToken) {
            $inviteUrl = route('register.invite', ['token' => $invitationToken]);
            return redirect()->route('employees.index')
                ->with('success', "Employee account created for {$employee->name}. An invitation link was generated (valid for 3 days): {$inviteUrl}")
                ->with('invite_url', $inviteUrl);
        }

        return redirect()->route('employees.index')
            ->with('success', "Employee {$employee->name} registered and activated successfully.");
    }

    // Resend / regenerate invitation link for employee
    public function resendInvitation(User $employee)
    {
        $token = Str::random(40);
        $employee->update([
            'invitation_token'      => $token,
            'invitation_expires_at' => now()->addDays(3),
            'is_active'             => false,
        ]);

        $inviteUrl = route('register.invite', ['token' => $token]);

        return back()->with('success', "New invitation generated for {$employee->name}: {$inviteUrl}")
            ->with('invite_url', $inviteUrl);
    }

    // Show edit form
    public function edit(User $employee)
    {
        $currentUser = auth()->user();

        // Managers cannot edit an administrator account
        if ($employee->role === 'admin' && $currentUser->role !== 'admin') {
            abort(403, 'Only administrators can edit admin accounts.');
        }

        return view('employees.edit', compact('employee'));
    }

    // Update employee info
    public function update(Request $request, User $employee)
    {
        $currentUser = auth()->user();

        // Managers cannot edit an administrator account
        if ($employee->role === 'admin' && $currentUser->role !== 'admin') {
            abort(403, 'Only administrators can edit admin accounts.');
        }

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $employee->id,
            'department' => 'required|string|max:255',
            'role'       => 'required|in:admin,manager,employee',
            'password'   => 'nullable|string|min:8',
        ]);

        // Non-admin users cannot promote accounts to admin
        if ($data['role'] === 'admin' && $currentUser->role !== 'admin') {
            abort(403, 'Only administrators can assign the admin role.');
        }

        $employee->name       = $data['name'];
        $employee->email      = $data['email'];
        $employee->department = $data['department'];
        $employee->role       = $data['role'];

        if (!empty($data['password'])) {
            $employee->password = Hash::make($data['password']);
        }

        $employee->save();

        return redirect()->route('employees.index')->with('success', 'Employee information updated successfully.');
    }

    // Toggle active / deactivated status
    public function toggleStatus(User $employee)
    {
        $currentUser = auth()->user();

        // Cannot deactivate yourself
        if ($employee->id === $currentUser->id) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        // Managers cannot deactivate admin accounts
        if ($employee->role === 'admin' && $currentUser->role !== 'admin') {
            abort(403, 'Only administrators can deactivate admin accounts.');
        }

        $employee->is_active = !$employee->is_active;
        $employee->save();

        $statusText = $employee->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Employee account has been {$statusText}.");
    }
}
