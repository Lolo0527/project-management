<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeManagementController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\TaskDetailsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\Manager\ProjectController;
use App\Http\Controllers\Manager\TaskController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use Illuminate\Support\Facades\Route;

// 1. Root & Guest Redirection
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'manager'  => redirect()->route('manager.dashboard'),
            'employee' => redirect()->route('employee.dashboard'),
            default    => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// 2. Guest Authentication & Registration Routes
Route::middleware('guest')->group(function () {
    // Dedicated Login Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Registration & Invitation Routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
    Route::get('/register/invite/{token}', [RegisterController::class, 'showInviteForm'])->name('register.invite');
    Route::post('/register/invite/{token}', [RegisterController::class, 'registerWithInvite'])->name('register.invite.submit');

    // Forgot & Reset Password Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// 3. Shared Authenticated Routes (Logout, Kanban, Task Details, Workflow & Notifications)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Kanban Board
    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::patch('/tasks/{task}/status', [KanbanController::class, 'updateStatus'])->name('tasks.update-status');

    // Task Details & Discussion Thread
    Route::get('/tasks/{task}', [TaskDetailsController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');

    // Task Review & Approval Workflow
    Route::post('/tasks/{task}/submit-review', [TaskDetailsController::class, 'submitForReview'])->name('tasks.submit-review');
    Route::post('/tasks/{task}/approve', [TaskDetailsController::class, 'approve'])->name('tasks.approve');
    Route::post('/tasks/{task}/request-correction', [TaskDetailsController::class, 'requestCorrection'])->name('tasks.request-correction');

    // Smart Notification Center
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

// 4. Employee Management Routes (Admin & Manager)
Route::middleware(['auth', 'role:admin,manager'])->prefix('employees')->name('employees.')->group(function () {
    Route::get('/', [EmployeeManagementController::class, 'index'])->name('index');
    Route::get('/create', [EmployeeManagementController::class, 'create'])->name('create');
    Route::post('/', [EmployeeManagementController::class, 'store'])->name('store');
    Route::post('/{employee}/resend-invite', [EmployeeManagementController::class, 'resendInvitation'])->name('resend-invite');
    Route::get('/{employee}/edit', [EmployeeManagementController::class, 'edit'])->name('edit');
    Route::put('/{employee}', [EmployeeManagementController::class, 'update'])->name('update');
    Route::patch('/{employee}/toggle-status', [EmployeeManagementController::class, 'toggleStatus'])->name('toggle-status');
});

// 5. Admin Routes (Admin Only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/register-employee', [EmployeeManagementController::class, 'create'])->name('register');
    Route::post('/register-employee', [EmployeeManagementController::class, 'store'])->name('register.store');
});

// 6. Manager & Admin Project Routes
Route::middleware(['auth', 'role:manager,admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

    // Project Management
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::patch('/projects/{project}/deadline', [ProjectController::class, 'updateDeadline'])->name('projects.update-deadline');
    Route::post('/projects/{project}/close', [ProjectController::class, 'closeProject'])->name('projects.close');

    // Task Assignment
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');

    // Productivity Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// 7. Employee Routes (Employee Only)
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'employee'])->name('dashboard');
});
