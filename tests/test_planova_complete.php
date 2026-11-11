<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function sendRequest($app, $kernel, $user, $method, $uri, $params = [], $files = []) {
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
echo "      PLANOVA COMPREHENSIVE SYSTEM VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck($condition, $label) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failCount++;
    }
}

// Fetch test actors
$admin = \App\Models\User::where('role', 'admin')->first();
$manager = \App\Models\User::where('role', 'manager')->first();
$employee1 = \App\Models\User::where('email', 'jane@company.com')->first();
$employee2 = \App\Models\User::where('email', 'john@company.com')->first();

// =================================================================
// 1. AUTHENTICATION & ROLE BARRIERS
// =================================================================
echo "1. Testing Role Barriers & Permissions (Task 1):\n";
$r1 = sendRequest($app, $kernel, $employee1, 'GET', '/admin/dashboard');
assertCheck($r1->getStatusCode() === 403, "Employee blocked from Admin Dashboard (403)");

$r2 = sendRequest($app, $kernel, $employee1, 'GET', '/manager/dashboard');
assertCheck($r2->getStatusCode() === 403, "Employee blocked from Manager Dashboard (403)");

$r3 = sendRequest($app, $kernel, $employee1, 'GET', '/manager/reports');
assertCheck($r3->getStatusCode() === 403, "Employee blocked from Manager Reports (403)");

$r4 = sendRequest($app, $kernel, $employee1, 'GET', '/employees');
assertCheck($r4->getStatusCode() === 403, "Employee blocked from Employee Management (403)");

$r5 = sendRequest($app, $kernel, $employee1, 'GET', '/employee/dashboard');
assertCheck($r5->getStatusCode() === 200, "Employee can access Employee Dashboard (200)");

$r6 = sendRequest($app, $kernel, $manager, 'GET', '/manager/dashboard');
assertCheck($r6->getStatusCode() === 200, "Manager can access Manager Dashboard (200)");

$r7 = sendRequest($app, $kernel, $admin, 'GET', '/admin/dashboard');
assertCheck($r7->getStatusCode() === 200, "Admin can access Admin Dashboard (200)");

// =================================================================
// 2. EMPLOYEE MANAGEMENT & ROLE ESCALATION DEFENSE
// =================================================================
echo "\n2. Testing Employee Management & Escalation Defense (Task 3):\n";
// Manager attempting to create an Admin account
$rMgrCreateAdmin = sendRequest($app, $kernel, $manager, 'POST', '/employees', [
    'name' => 'Illegal Admin',
    'email' => 'hacker_admin@company.com',
    'department' => 'IT',
    'role' => 'admin',
    'password' => 'secret123',
]);
assertCheck($rMgrCreateAdmin->getStatusCode() === 403, "Manager prohibited from creating an Admin account (403)");

// Admin creating a legitimate employee
$testEmail = 'newhire_' . time() . '@company.com';
$rAdminCreateEmp = sendRequest($app, $kernel, $admin, 'POST', '/employees', [
    'name' => 'New Hire',
    'email' => $testEmail,
    'department' => 'Quality Assurance',
    'role' => 'employee',
    'password' => 'password123',
]);
$newEmp = \App\Models\User::where('email', $testEmail)->first();
assertCheck($rAdminCreateEmp->getStatusCode() === 302 && $newEmp !== null, "Admin successfully registered new employee");

// Admin deactivation toggle
$rToggle = sendRequest($app, $kernel, $admin, 'PATCH', "/employees/{$newEmp->id}/toggle-status");
$newEmp->refresh();
assertCheck($newEmp->is_active === false, "Account deactivation toggled successfully");

// =================================================================
// 3. PROJECT CREATION, EDITING & DEADLINE UPDATE (Task 4)
// =================================================================
echo "\n3. Testing Project Creation & Editing (Task 4):\n";
$projectTitle = 'Alpha Portal ' . time();
$rCreateProj = sendRequest($app, $kernel, $manager, 'POST', '/manager/projects', [
    'title' => $projectTitle,
    'description' => 'Test project for full lifecycle audit',
    'start_date' => date('Y-m-d'),
    'deadline' => date('Y-m-d', strtotime('+30 days')),
    'manager_id' => $manager->id,
    'priority' => 'high',
    'team_members' => [$employee1->id, $employee2->id],
]);
$testProject = \App\Models\Project::where('title', $projectTitle)->first();
assertCheck($rCreateProj->getStatusCode() === 302 && $testProject !== null, "Manager created project with team members");

// Test Edit View
$rEditView = sendRequest($app, $kernel, $manager, 'GET', "/manager/projects/{$testProject->id}/edit");
assertCheck($rEditView->getStatusCode() === 200, "Manager can access Project Edit form (200)");

// Test Project Update (PUT)
$newDeadline = date('Y-m-d', strtotime('+45 days'));
$rUpdateProj = sendRequest($app, $kernel, $manager, 'PUT', "/manager/projects/{$testProject->id}", [
    'title' => $projectTitle . ' (Updated)',
    'description' => 'Updated project description',
    'start_date' => date('Y-m-d'),
    'deadline' => $newDeadline,
    'manager_id' => $manager->id,
    'priority' => 'medium',
    'status' => 'active',
    'team_members' => [$employee1->id, $employee2->id],
]);
$testProject->refresh();
assertCheck(
    $rUpdateProj->getStatusCode() === 302 && 
    $testProject->title === $projectTitle . ' (Updated)' && 
    $testProject->deadline === $newDeadline &&
    $testProject->priority === 'medium',
    "Manager updated project details, priority, and deadline"
);

// =================================================================
// 4. TASK CREATION & ASSIGNMENT (Task 5)
// =================================================================
echo "\n4. Testing Task Creation & Assignment (Task 5):\n";
$rCreateTask1 = sendRequest($app, $kernel, $manager, 'POST', "/manager/projects/{$testProject->id}/tasks", [
    'title' => 'Implement Frontend Components',
    'description' => 'Build responsive UI components',
    'assigned_to' => $employee1->id,
    'priority' => 'high',
    'start_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d', strtotime('+10 days')),
    'status' => 'pending',
]);
$task1 = $testProject->tasks()->where('title', 'Implement Frontend Components')->first();
assertCheck($rCreateTask1->getStatusCode() === 302 && $task1 !== null, "Task 1 assigned to Employee 1");

$rCreateTask2 = sendRequest($app, $kernel, $manager, 'POST', "/manager/projects/{$testProject->id}/tasks", [
    'title' => 'Setup Database Schema',
    'description' => 'Configure tables and foreign keys',
    'assigned_to' => $employee2->id,
    'priority' => 'medium',
    'start_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d', strtotime('+15 days')),
    'status' => 'pending',
]);
$task2 = $testProject->tasks()->where('title', 'Setup Database Schema')->first();
assertCheck($rCreateTask2->getStatusCode() === 302 && $task2 !== null, "Task 2 assigned to Employee 2");

// Verify Employee 1 received task notification
$emp1Notif = $employee1->notifications()->where('data', 'like', '%Implement Frontend Components%')->first();
assertCheck(
    $emp1Notif !== null,
    "Employee 1 received notification upon task assignment"
);

// =================================================================
// 5. KANBAN BOARD ISOLATION & STATUS ENFORCEMENT (Task 6)
// =================================================================
echo "\n5. Testing Kanban Board Isolation & Status Security (Task 6):\n";
// Employee 1 loads Kanban
$rKanbanEmp1 = sendRequest($app, $kernel, $employee1, 'GET', '/kanban');
assertCheck($rKanbanEmp1->getStatusCode() === 200, "Employee loads Kanban board");

// Employee 1 attempts to move Employee 2's task
$rIllegalMove = sendRequest($app, $kernel, $employee1, 'PATCH', "/tasks/{$task2->id}/status", [
    'status' => 'in_progress',
]);
assertCheck($rIllegalMove->getStatusCode() === 403, "Employee prohibited from moving someone else's task (403)");

// Employee 1 moves their own task to in_progress
$rLegalMove = sendRequest($app, $kernel, $employee1, 'PATCH', "/tasks/{$task1->id}/status", [
    'status' => 'in_progress',
]);
$task1->refresh();
assertCheck($rLegalMove->getStatusCode() === 302 && $task1->status === 'in_progress', "Employee 1 moved assigned task to in_progress");

// =================================================================
// 6. COMMENTS, ATTACHMENTS & ACCESS GUARDS (Task 7)
// =================================================================
echo "\n6. Testing Comments, Attachments & Access Guards (Task 7):\n";
// Unauthorized user (e.g. deactivated or unassigned user) attempting to view task
$outsider = \App\Models\User::where('role', 'employee')->where('id', '!=', $employee1->id)->where('id', '!=', $employee2->id)->first();
if ($outsider) {
    $rOutsiderView = sendRequest($app, $kernel, $outsider, 'GET', "/tasks/{$task1->id}");
    assertCheck($rOutsiderView->getStatusCode() === 403, "Unassigned outsider blocked from viewing task details (403)");

    $rOutsiderComment = sendRequest($app, $kernel, $outsider, 'POST', "/tasks/{$task1->id}/comments", [
        'content' => 'Malicious comment',
    ]);
    assertCheck($rOutsiderComment->getStatusCode() === 403, "Unassigned outsider blocked from commenting on task (403)");
}

// Authorized Employee 1 posts comment in task
$rComment = sendRequest($app, $kernel, $employee1, 'POST', "/tasks/{$task1->id}/comments", [
    'content' => 'Draft UI layout completed and uploaded for review.',
]);
$comment = $task1->comments()->latest()->first();
assertCheck($rComment->getStatusCode() === 302 && $comment->content === 'Draft UI layout completed and uploaded for review.', "Assigned employee posted in discussion thread");

// =================================================================
// 7. TASK REVIEW & APPROVAL WORKFLOW (Task 10)
// =================================================================
echo "\n7. Testing Task Review & Approval Lifecycle (Task 10):\n";
// Step 1: Employee submits work for review
$rSubmitWork = sendRequest($app, $kernel, $employee1, 'POST', "/tasks/{$task1->id}/submit-review", [
    'submission_notes' => 'All layout requirements implemented.',
]);
$task1->refresh();
assertCheck(
    $rSubmitWork->getStatusCode() === 302 && 
    $task1->status === 'completed' && 
    $task1->is_approved === false &&
    $task1->submission_notes === 'All layout requirements implemented.',
    "Employee submitted work for review (status: completed, is_approved: false)"
);

// Step 2: Manager requests corrections
$rRequestCorrection = sendRequest($app, $kernel, $manager, 'POST', "/tasks/{$task1->id}/request-correction", [
    'revision_notes' => 'Please adjust button padding on mobile view.',
]);
$task1->refresh();
assertCheck(
    $rRequestCorrection->getStatusCode() === 302 && 
    $task1->status === 'in_progress' && 
    $task1->is_approved === false,
    "Manager requested corrections (status reverted to in_progress)"
);

// Step 3: Employee resubmits work
$rResubmit = sendRequest($app, $kernel, $employee1, 'POST', "/tasks/{$task1->id}/submit-review", [
    'submission_notes' => 'Button padding adjusted for mobile screens.',
]);
$task1->refresh();
assertCheck($task1->status === 'completed' && $task1->is_approved === false, "Employee resubmitted corrected work");

// Step 4: Manager approves work
$rApproveTask1 = sendRequest($app, $kernel, $manager, 'POST', "/tasks/{$task1->id}/approve");
$task1->refresh();
assertCheck(
    $rApproveTask1->getStatusCode() === 302 && 
    $task1->status === 'completed' && 
    $task1->is_approved === true,
    "Manager approved Task 1 (is_approved: true)"
);

// Step 5: Test Project Closure Barrier (Task 2 still unapproved)
$rPrematureClose = sendRequest($app, $kernel, $manager, 'POST', "/manager/projects/{$testProject->id}/close");
$testProject->refresh();
assertCheck(
    $testProject->status === 'active',
    "Project closure prevented while Task 2 is still unapproved"
);

// Approve Task 2 to unblock project closure
$task2->update(['status' => 'completed', 'is_approved' => true]);

// Step 6: Manager closes and completes project
$rCloseProject = sendRequest($app, $kernel, $manager, 'POST', "/manager/projects/{$testProject->id}/close");
$testProject->refresh();
assertCheck(
    $rCloseProject->getStatusCode() === 302 && 
    $testProject->status === 'completed',
    "Manager successfully closed and completed the project (status: completed)"
);

// =================================================================
// 8. NOTIFICATION TRIGGERS (Task 8)
// =================================================================
echo "\n8. Testing Notification Triggers (Task 8):\n";
$managerNotif = $manager->notifications()->latest()->first();
assertCheck(
    $managerNotif !== null,
    "Manager received notification in Smart Notification Center"
);

// Test Mark as Read
$rRead = sendRequest($app, $kernel, $manager, 'PATCH', "/notifications/{$managerNotif->id}/read");
$managerNotif->refresh();
assertCheck($managerNotif->read_at !== null, "Notification marked as read");

// =================================================================
// 9. PRODUCTIVITY REPORTS (Task 9)
// =================================================================
echo "\n9. Testing Productivity Reports (Task 9):\n";
$rReports = sendRequest($app, $kernel, $manager, 'GET', '/manager/reports');
assertCheck($rReports->getStatusCode() === 200, "Manager can load Productivity Reports (200)");

$rAdminReports = sendRequest($app, $kernel, $admin, 'GET', '/manager/reports');
assertCheck($rAdminReports->getStatusCode() === 200, "Admin can load Productivity Reports (200)");

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
