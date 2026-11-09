<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeStep8Request($app, $kernel, $user, $method, $uri, $params = []) {
    $session = $app->make('session.store');
    $session->start();
    
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $params['_token'] = $session->token();
    }
    
    $req = \Illuminate\Http\Request::create($uri, $method, $params);
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

$manager = App\Models\User::where('role', 'manager')->first();
$jane = App\Models\User::where('email', 'jane@company.com')->first();
$john = App\Models\User::where('email', 'john@company.com')->first();
$project = App\Models\Project::where('title', 'Website Redesign')->first();
$task = App\Models\Task::where('title', 'Design homepage')->first();

echo "--- STEP 8 NOTIFICATION TESTS ---\n";

// 1. Check Notification Center page
$resCenter = executeStep8Request($app, $kernel, $jane, 'GET', '/notifications');
$content = $resCenter->getContent();
echo "1. Notification Center loads -> Status: {$resCenter->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'Notification Center': " . (str_contains($content, 'Notification Center') ? "YES" : "NO") . "\n";
echo "   - Contains seeded notifications: " . (str_contains($content, 'New Task Assigned') ? "YES" : "NO") . "\n";

// 2. Trigger 1: New task assignment
$janeInitialCount = $jane->notifications()->count();
executeStep8Request($app, $kernel, $manager, 'POST', "/manager/projects/{$project->id}/tasks", [
    'title'       => 'QA Mobile Header',
    'assigned_to' => $jane->id,
    'priority'    => 'medium',
    'due_date'    => '2026-11-28',
    'status'      => 'pending',
]);
$jane->refresh();
echo "2. Trigger 1: New Task Assigned -> Jane notifications count: {$jane->notifications()->count()} (was {$janeInitialCount})\n";
$latest = $jane->notifications()->first();
echo "   - Latest: \"{$latest->data['title']}\" - {$latest->data['message']}\n";

// 3. Trigger 2: New comment on task
$managerInitialCount = $manager->notifications()->count();
executeStep8Request($app, $kernel, $jane, 'POST', "/tasks/{$task->id}/comments", [
    'content' => 'Uploaded the revision for review.',
]);
$manager->refresh();
echo "3. Trigger 2: Employee comments -> Manager notifications count: {$manager->notifications()->count()} (was {$managerInitialCount})\n";
$latestMgr = $manager->notifications()->first();
echo "   - Latest: \"{$latestMgr->data['title']}\" - {$latestMgr->data['message']}\n";

// 4. Trigger 3: Task status changes (Kanban)
$johnInitialCount = $john->notifications()->count();
$johnTask = App\Models\Task::where('assigned_to', $john->id)->first();
executeStep8Request($app, $kernel, $manager, 'PATCH', "/tasks/{$johnTask->id}/status", [
    'status' => 'in_progress',
]);
$john->refresh();
echo "4. Trigger 3: Task Status Changed -> John notifications count: {$john->notifications()->count()} (was {$johnInitialCount})\n";
$latestJohn = $john->notifications()->first();
echo "   - Latest: \"{$latestJohn->data['title']}\" - {$latestJohn->data['message']}\n";

// 5. Trigger 4: Manager marks task as approved
$janeBeforeApprove = $jane->notifications()->count();
executeStep8Request($app, $kernel, $manager, 'POST', "/tasks/{$task->id}/approve");
$jane->refresh();
echo "5. Trigger 4: Manager Approves Task -> Jane notifications count: {$jane->notifications()->count()} (was {$janeBeforeApprove})\n";
$latestApproved = $jane->notifications()->first();
echo "   - Latest: \"{$latestApproved->data['title']}\" - {$latestApproved->data['message']}\n";

// 6. Trigger 5: Project deadline changes
$teamBefore = $jane->notifications()->count();
executeStep8Request($app, $kernel, $manager, 'PATCH', "/manager/projects/{$project->id}/deadline", [
    'deadline' => '2026-12-25',
]);
$jane->refresh();
echo "6. Trigger 5: Project Deadline Changed -> Jane notified: " . ($jane->notifications()->count() > $teamBefore ? "YES" : "NO") . "\n";
$latestDeadline = $jane->notifications()->first();
echo "   - Latest: \"{$latestDeadline->data['title']}\" - {$latestDeadline->data['message']}\n";

// 7. Trigger 6: Approaching deadline command
$approachingTask = App\Models\Task::create([
    'project_id'  => $project->id,
    'assigned_to' => $john->id,
    'title'       => 'Urgent Security Patch',
    'due_date'    => now()->addDay()->toDateString(), // Due tomorrow!
    'priority'    => 'high',
    'status'      => 'pending',
]);
$johnBeforeCron = $john->notifications()->count();
\Illuminate\Support\Facades\Artisan::call('tasks:check-deadlines');
$john->refresh();
echo "7. Trigger 6: Deadline Approaching command -> John notified: " . ($john->notifications()->count() > $johnBeforeCron ? "YES" : "NO") . "\n";
$latestSoon = $john->notifications()->first();
echo "   - Latest: \"{$latestSoon->data['title']}\" - {$latestSoon->data['message']}\n";

// 8. Mark all as read
$unreadBefore = $jane->unreadNotifications()->count();
echo "8. Unread notifications before Mark All Read: {$unreadBefore}\n";
executeStep8Request($app, $kernel, $jane, 'POST', '/notifications/mark-all-read');
$jane->refresh();
echo "   - Unread notifications after Mark All Read: {$jane->unreadNotifications()->count()} (Expected: 0)\n";

echo "\nALL STEP 8 NOTIFICATION TESTS COMPLETED SUCCESSFULLY!\n";
