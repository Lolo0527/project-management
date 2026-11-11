<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function sendReq($app, $kernel, $user, $method, $uri, $params = [], $files = []) {
    $session = $app->make('session.store');
    $session->start();

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $params['_token'] = $session->token();
    }

    $req = \Illuminate\Http\Request::create($uri, $method, $params, [], $files);
    $req->setLaravelSession($session);
    $app->instance('request', $req);

    if ($user) {
        \Illuminate\Support\Facades\Auth::setUser($user);
    } else {
        \Illuminate\Support\Facades\Auth::logout();
    }

    $res = $kernel->handle($req);
    $kernel->terminate($req, $res);
    return $res;
}

echo "=================================================================\n";
echo "       PLANOVA V2 AUTH, SECURITY & SYSTEM VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($condition, $label) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// 1. GET /login AND SPLIT-SCREEN AUTH PAGE
// -------------------------------------------------------------
echo "1. Testing Dedicated /login Page & Guest Routing:\n";
$rGetLogin = sendReq($app, $kernel, null, 'GET', '/login');
assertTest($rGetLogin->getStatusCode() === 200, "Dedicated GET /login returns 200 OK");
assertTest(str_contains($rGetLogin->getContent(), 'Everything your team needs to get work done.'), "Auth page contains Planova SaaS headline");
assertTest(str_contains($rGetLogin->getContent(), 'Project Management') && str_contains($rGetLogin->getContent(), 'Productivity Reports'), "Auth page contains the 4 feature descriptions");
assertTest(str_contains($rGetLogin->getContent(), 'toggleTheme'), "Auth page contains theme switcher");
assertTest(str_contains($rGetLogin->getContent(), 'togglePassword'), "Auth page contains show/hide password toggle");

// Root / redirects guest to /login
$rRoot = sendReq($app, $kernel, null, 'GET', '/');
assertTest($rRoot->getStatusCode() === 302 && str_contains($rRoot->headers->get('Location'), '/login'), "Root / redirects guest to /login");

// -------------------------------------------------------------
// 2. AUTHENTICATION & ROLE-BASED DASHBOARD REDIRECTION
// -------------------------------------------------------------
echo "\n2. Testing Authentication & Role Redirection:\n";
// Admin Login
$rAdminLogin = sendReq($app, $kernel, null, 'POST', '/login', [
    'email'    => 'admin@company.com',
    'password' => 'password123',
    'remember' => '1',
]);
assertTest($rAdminLogin->getStatusCode() === 302 && str_contains($rAdminLogin->headers->get('Location'), 'admin/dashboard'), "Admin login redirects to Admin Dashboard");

// Manager Login
$rMgrLogin = sendReq($app, $kernel, null, 'POST', '/login', [
    'email'    => 'manager@company.com',
    'password' => 'password123',
]);
assertTest($rMgrLogin->getStatusCode() === 302 && str_contains($rMgrLogin->headers->get('Location'), 'manager/dashboard'), "Manager login redirects to Manager Dashboard");

// Employee Login
$rEmpLogin = sendReq($app, $kernel, null, 'POST', '/login', [
    'email'    => 'jane@company.com',
    'password' => 'password123',
]);
assertTest($rEmpLogin->getStatusCode() === 302 && str_contains($rEmpLogin->headers->get('Location'), 'employee/dashboard'), "Employee login redirects to Employee Dashboard");

// Incorrect Password
$rBadLogin = sendReq($app, $kernel, null, 'POST', '/login', [
    'email'    => 'jane@company.com',
    'password' => 'wrong_password_999',
]);
assertTest($rBadLogin->getStatusCode() === 302 && $rBadLogin->headers->get('Location') !== '/employee/dashboard', "Incorrect password fails login and returns back");

// Inactive Account
$inactiveUser = \App\Models\User::firstOrCreate(
    ['email' => 'deactivated_test@company.com'],
    [
        'name'       => 'Deactivated Test',
        'department' => 'Operations',
        'role'       => 'employee',
        'password'   => \Illuminate\Support\Facades\Hash::make('password123'),
        'is_active'  => false,
    ]
);
$rInactiveLogin = sendReq($app, $kernel, null, 'POST', '/login', [
    'email'    => 'deactivated_test@company.com',
    'password' => 'password123',
]);
assertTest($rInactiveLogin->getStatusCode() === 302 && !auth()->check(), "Deactivated account prevented from logging in");

// -------------------------------------------------------------
// 3. INVITATION-BASED REGISTRATION PROCESS
// -------------------------------------------------------------
echo "\n3. Testing Invitation-Based Registration Process:\n";
$admin = \App\Models\User::where('role', 'admin')->first();

// Step 1: Admin invites employee
$invitedEmail = 'invitee_' . time() . '@company.com';
$rInviteCreate = sendReq($app, $kernel, $admin, 'POST', '/employees', [
    'name'       => 'Alice Invitee',
    'email'      => $invitedEmail,
    'department' => 'Frontend Architecture',
    'role'       => 'employee',
]);
$invitedUser = \App\Models\User::where('email', $invitedEmail)->first();
assertTest(
    $rInviteCreate->getStatusCode() === 302 && 
    $invitedUser !== null && 
    $invitedUser->is_active === false && 
    !empty($invitedUser->invitation_token),
    "Admin generated employee with inactive status and secure invitation token"
);

// Verify success message does NOT expose raw generated password
$sessionStore = $app->make('session.store');
$successMsg = $sessionStore->get('success');
assertTest(!str_contains($successMsg, 'Password:'), "Generated passwords are not displayed in success messages");

// Step 2: Employee opens invitation link
$token = $invitedUser->invitation_token;
$rOpenInvite = sendReq($app, $kernel, null, 'GET', "/register/invite/{$token}");
assertTest($rOpenInvite->getStatusCode() === 200, "Employee accesses invitation page (200 OK)");

// Step 3: Employee completes registration
$rCompleteReg = sendReq($app, $kernel, null, 'POST', "/register/invite/{$token}", [
    'name'                  => 'Alice Invitee Updated',
    'department'            => 'Frontend Architecture',
    'password'              => 'secureP@ssw0rd2026',
    'password_confirmation' => 'secureP@ssw0rd2026',
    'terms'                 => '1',
]);
$invitedUser->refresh();
assertTest(
    $rCompleteReg->getStatusCode() === 302 && 
    $invitedUser->is_active === true && 
    $invitedUser->invitation_token === null &&
    \Illuminate\Support\Facades\Hash::check('secureP@ssw0rd2026', $invitedUser->password),
    "Employee completed registration, created password, and account is activated"
);

// Step 4: Employee can log in with new credentials
$rNewLogin = sendReq($app, $kernel, null, 'POST', '/login', [
    'email'    => $invitedEmail,
    'password' => 'secureP@ssw0rd2026',
]);
assertTest($rNewLogin->getStatusCode() === 302 && str_contains($rNewLogin->headers->get('Location'), 'employee/dashboard'), "New employee logs in and redirects to Employee Dashboard");

// -------------------------------------------------------------
// 4. EXPIRED INVITATION HANDLING
// -------------------------------------------------------------
echo "\n4. Testing Expired Invitation Handling:\n";
$expiredUser = \App\Models\User::create([
    'name'                  => 'Bob Expired',
    'email'                 => 'expired_' . time() . '@company.com',
    'department'            => 'Design',
    'role'                  => 'employee',
    'password'              => \Illuminate\Support\Facades\Hash::make('random123'),
    'is_active'             => false,
    'invitation_token'      => 'expired_token_' . time(),
    'invitation_expires_at' => now()->subDay(), // Expired 1 day ago
]);

$rExpiredAccess = sendReq($app, $kernel, null, 'GET', "/register/invite/{$expiredUser->invitation_token}");
assertTest($rExpiredAccess->getStatusCode() === 302, "Expired invitation link rejected and redirected to login");

// -------------------------------------------------------------
// 5. FORGOT PASSWORD & PASSWORD RESET
// -------------------------------------------------------------
echo "\n5. Testing Forgot Password & Password Reset Flow:\n";
$rForgotReq = sendReq($app, $kernel, null, 'POST', '/forgot-password', [
    'email' => 'jane@company.com',
]);
$resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', 'jane@company.com')->first();
assertTest($rForgotReq->getStatusCode() === 302 && $resetRecord !== null, "Password reset token generated in database");

// Simulate user resetting password
$plainResetToken = 'dummy_token_' . time();
\Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => 'jane@company.com'],
    ['token' => \Illuminate\Support\Facades\Hash::make($plainResetToken), 'created_at' => now()]
);

$rResetSubmit = sendReq($app, $kernel, null, 'POST', '/reset-password', [
    'token'                 => $plainResetToken,
    'email'                 => 'jane@company.com',
    'password'              => 'newP@ssword2026',
    'password_confirmation' => 'newP@ssword2026',
]);
$jane = \App\Models\User::where('email', 'jane@company.com')->first();
assertTest(
    $rResetSubmit->getStatusCode() === 302 && 
    \Illuminate\Support\Facades\Hash::check('newP@ssword2026', $jane->password),
    "Password successfully updated via reset form"
);

// Restore Jane's password for consistent suite runs
$jane->update(['password' => \Illuminate\Support\Facades\Hash::make('password123')]);

// -------------------------------------------------------------
// 6. MANAGER PROJECT OWNERSHIP & AUTHORIZATION GUARDS
// -------------------------------------------------------------
echo "\n6. Testing Manager Project Ownership & Authorization (Req 4 & 5):\n";
$manager1 = \App\Models\User::where('role', 'manager')->first();
$manager2 = \App\Models\User::where('role', 'manager')->where('id', '!=', $manager1->id)->first();

if (!$manager2) {
    $manager2 = \App\Models\User::create([
        'name'       => 'Manager Two',
        'email'      => 'manager2@company.com',
        'department' => 'Operations',
        'role'       => 'manager',
        'password'   => \Illuminate\Support\Facades\Hash::make('password123'),
        'is_active'  => true,
    ]);
}

// Create a project managed strictly by Manager 1
$proj1 = \App\Models\Project::create([
    'title'       => 'Manager 1 Secret Project ' . time(),
    'description' => 'Confidential project',
    'start_date'  => date('Y-m-d'),
    'deadline'    => date('Y-m-d', strtotime('+20 days')),
    'manager_id'  => $manager1->id,
    'priority'    => 'high',
    'status'      => 'active',
]);

// Manager 2 attempts to edit Manager 1's project -> MUST BE 403
$rIllegalEdit = sendReq($app, $kernel, $manager2, 'GET', "/manager/projects/{$proj1->id}/edit");
assertTest($rIllegalEdit->getStatusCode() === 403, "Manager 2 blocked from editing Manager 1's project (403)");

// Manager 2 attempts to update Manager 1's project -> MUST BE 403
$rIllegalUpdate = sendReq($app, $kernel, $manager2, 'PUT', "/manager/projects/{$proj1->id}", [
    'title'        => 'Hijacked Title',
    'start_date'   => date('Y-m-d'),
    'deadline'     => date('Y-m-d', strtotime('+30 days')),
    'manager_id'   => $manager2->id,
    'priority'     => 'low',
    'status'       => 'active',
]);
assertTest($rIllegalUpdate->getStatusCode() === 403, "Manager 2 blocked from updating Manager 1's project (403)");

// Manager 2 attempts to close Manager 1's project -> MUST BE 403
$rIllegalClose = sendReq($app, $kernel, $manager2, 'POST', "/manager/projects/{$proj1->id}/close");
assertTest($rIllegalClose->getStatusCode() === 403, "Manager 2 blocked from closing Manager 1's project (403)");

// Manager 1 (Owner) CAN access edit form
$rLegalEdit = sendReq($app, $kernel, $manager1, 'GET', "/manager/projects/{$proj1->id}/edit");
assertTest($rLegalEdit->getStatusCode() === 200, "Manager 1 (Owner) can access project edit form (200 OK)");

// -------------------------------------------------------------
// 7. TASK ASSIGNMENT & ACTIVE TEAM MEMBER VALIDATION (Req 8 & 9)
// -------------------------------------------------------------
echo "\n7. Testing Task Assignee & Active Member Validation (Req 8 & 9):\n";
$empActive1 = \App\Models\User::where('email', 'john@company.com')->first();
$outsiderEmp = \App\Models\User::where('email', 'alex@company.com')->first();

// Assign only John to Project 1
$proj1->members()->sync([$empActive1->id]);

// Manager 1 attempts to assign task to Alex (who is NOT on Project 1)
$rIllegalTaskAssign = sendReq($app, $kernel, $manager1, 'POST', "/manager/projects/{$proj1->id}/tasks", [
    'title'       => 'Task for Non-Member',
    'assigned_to' => $outsiderEmp->id,
    'priority'    => 'medium',
    'due_date'    => date('Y-m-d', strtotime('+7 days')),
    'status'      => 'pending',
]);
assertTest(
    $rIllegalTaskAssign->getStatusCode() === 302 && 
    $proj1->tasks()->where('title', 'Task for Non-Member')->count() === 0,
    "Task assignment rejected when employee is not a member of the project"
);

// Manager 1 assigns task to John (valid team member)
$rLegalTaskAssign = sendReq($app, $kernel, $manager1, 'POST', "/manager/projects/{$proj1->id}/tasks", [
    'title'       => 'Task for Project Member',
    'assigned_to' => $empActive1->id,
    'priority'    => 'medium',
    'due_date'    => date('Y-m-d', strtotime('+7 days')),
    'status'      => 'pending',
]);
$validTask = $proj1->tasks()->where('title', 'Task for Project Member')->first();
assertTest($rLegalTaskAssign->getStatusCode() === 302 && $validTask !== null, "Task successfully assigned to project team member");

// -------------------------------------------------------------
// 8. DIRECT PROJECT COMPLETION BYPASS PREVENTION (Req 10)
// -------------------------------------------------------------
echo "\n8. Testing Prevention of Direct Project Completion Bypass (Req 10):\n";
// Attempting to directly set project status to completed via PUT while tasks are unapproved
$rBypassAttempt = sendReq($app, $kernel, $manager1, 'PUT', "/manager/projects/{$proj1->id}", [
    'title'        => $proj1->title,
    'start_date'   => $proj1->start_date,
    'deadline'     => $proj1->deadline,
    'manager_id'   => $manager1->id,
    'priority'     => 'high',
    'status'       => 'completed', // Bypassing workflow!
    'team_members' => [$empActive1->id],
]);
$proj1->refresh();
assertTest($proj1->status === 'active', "Direct project completion prevented while unapproved tasks remain");

// -------------------------------------------------------------
// 9. THEME SWITCHER & TOKENS VALIDATION (Part 2)
// -------------------------------------------------------------
echo "\n9. Testing Dark/Light Mode Tokens & Layout:\n";
$rAppLayout = sendReq($app, $kernel, $manager1, 'GET', '/kanban');
assertTest(str_contains($rAppLayout->getContent(), 'toggleTheme()'), "Main application navigation includes theme switcher");
assertTest(str_contains($rAppLayout->getContent(), 'data-theme'), "Layout supports data-theme light and dark mode");

// -------------------------------------------------------------
// 10. PRODUCTIVITY REPORTS ACCESS & METRICS (Part 5)
// -------------------------------------------------------------
echo "\n10. Testing Productivity Reports:\n";
$rReports = sendReq($app, $kernel, $manager1, 'GET', '/manager/reports');
assertTest($rReports->getStatusCode() === 200, "Manager can view productivity reports (200 OK)");

echo "\n=================================================================\n";
echo "                      TEST SUMMARY\n";
echo "=================================================================\n";
echo "Total Passed: {$passCount}\n";
echo "Total Failed: {$failCount}\n";
if ($failCount === 0) {
    echo "RESULT: ALL TESTS PASSED SUCCESSFULLY! (100% SUCCESS RATE)\n";
} else {
    echo "RESULT: SOME TESTS FAILED!\n";
}
echo "=================================================================\n";
