<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function testReq($app, $kernel, $user, $method, $uri, $params = [], $files = []) {
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

$admin = App\Models\User::where('role', 'admin')->first();
$manager = App\Models\User::where('role', 'manager')->first();
$jane = App\Models\User::where('email', 'jane@company.com')->first();
$project = App\Models\Project::where('title', 'Website Redesign')->first();
$task = App\Models\Task::where('title', 'Design homepage')->first();

echo "=========================================\n";
echo "       COMPREHENSIVE AUDIT & VERIFICATION\n";
echo "=========================================\n\n";

// TEST 1: Dashboards
echo "1. Checking Dashboard Views:\n";
$rAdmin = testReq($app, $kernel, $admin, 'GET', '/admin/dashboard');
echo "   - Admin Dashboard: " . ($rAdmin->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rAdmin->getStatusCode()})") . "\n";

$rManager = testReq($app, $kernel, $manager, 'GET', '/manager/dashboard');
echo "   - Manager Dashboard: " . ($rManager->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rManager->getStatusCode()})") . "\n";

$rEmployee = testReq($app, $kernel, $jane, 'GET', '/employee/dashboard');
echo "   - Employee Dashboard: " . ($rEmployee->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rEmployee->getStatusCode()})") . "\n";

// TEST 2: Project Details & Update Deadline Route
echo "\n2. Checking Project Details & Deadline Route:\n";
$rProj = testReq($app, $kernel, $manager, 'GET', "/manager/projects/{$project->id}");
echo "   - Manager View Project: " . ($rProj->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rProj->getStatusCode()})") . "\n";

$rAdminProj = testReq($app, $kernel, $admin, 'GET', "/manager/projects/{$project->id}");
echo "   - Admin View Project: " . ($rAdminProj->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rAdminProj->getStatusCode()})") . "\n";

$rDeadline = testReq($app, $kernel, $manager, 'PATCH', "/manager/projects/{$project->id}/deadline", [
    'deadline' => '2026-12-28',
]);
$project->refresh();
echo "   - Update Project Deadline Route: " . ($rDeadline->getStatusCode() === 302 && $project->deadline === '2026-12-28' ? "PASS" : "FAIL") . "\n";

// TEST 3: Kanban Board & Status Updates
echo "\n3. Checking Kanban Board:\n";
$rKanban = testReq($app, $kernel, $jane, 'GET', '/kanban');
echo "   - Load Kanban Page: " . ($rKanban->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rKanban->getStatusCode()})") . "\n";

$rStatus = testReq($app, $kernel, $jane, 'PATCH', "/tasks/{$task->id}/status", [
    'status' => 'completed',
]);
$task->refresh();
echo "   - Update Task Status via PATCH: " . ($task->status === 'completed' ? "PASS" : "FAIL") . "\n";

// TEST 4: Task Details & Comments Feed
echo "\n4. Checking Task Details & Comments:\n";
$rTaskShow = testReq($app, $kernel, $jane, 'GET', "/tasks/{$task->id}");
echo "   - Task Details Page: " . ($rTaskShow->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rTaskShow->getStatusCode()})") . "\n";

$rPostComment = testReq($app, $kernel, $jane, 'POST', "/tasks/{$task->id}/comments", [
    'content' => 'Audit check comment from Jane.',
]);
echo "   - Post Comment in Thread: " . ($rPostComment->getStatusCode() === 302 ? "PASS" : "FAIL") . "\n";

// TEST 5: Smart Notification Center
echo "\n5. Checking Notification Center:\n";
$rNotif = testReq($app, $kernel, $jane, 'GET', '/notifications');
echo "   - Notification Center Page: " . ($rNotif->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rNotif->getStatusCode()})") . "\n";

$rMarkAll = testReq($app, $kernel, $jane, 'POST', '/notifications/mark-all-read');
echo "   - Mark All As Read: " . ($rMarkAll->getStatusCode() === 302 ? "PASS" : "FAIL") . "\n";

// TEST 6: Productivity Reports
echo "\n6. Checking Productivity Reports:\n";
$rReports = testReq($app, $kernel, $manager, 'GET', '/manager/reports');
echo "   - Manager View Reports: " . ($rReports->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rReports->getStatusCode()})") . "\n";

$rAdminReports = testReq($app, $kernel, $admin, 'GET', '/manager/reports');
echo "   - Admin View Reports: " . ($rAdminReports->getStatusCode() === 200 ? "PASS (200)" : "FAIL ({$rAdminReports->getStatusCode()})") . "\n";

$rEmpReports = testReq($app, $kernel, $jane, 'GET', '/manager/reports');
echo "   - Employee Access Blocked: " . ($rEmpReports->getStatusCode() === 403 ? "PASS (403)" : "FAIL ({$rEmpReports->getStatusCode()})") . "\n";

echo "\n=========================================\n";
echo "       ALL AUDIT TESTS COMPLETED!\n";
echo "=========================================\n";
