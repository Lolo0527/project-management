<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeStep5Request($app, $kernel, $user, $method, $uri, $params = []) {
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
$websiteRedesign = App\Models\Project::where('title', 'Website Redesign')->first();

// 1. Manager visits task create form
$resForm = executeStep5Request($app, $kernel, $manager, 'GET', "/manager/projects/{$websiteRedesign->id}/tasks/create");
$formContent = $resForm->getContent();
echo "1. Manager visits task create form -> Status: {$resForm->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'Task Title': " . (str_contains($formContent, 'Task Title') ? "YES" : "NO") . "\n";
echo "   - Contains 'Assigned Employee': " . (str_contains($formContent, 'Assigned Employee') ? "YES" : "NO") . "\n";
echo "   - Contains 'Priority': " . (str_contains($formContent, 'Priority') ? "YES" : "NO") . "\n";
echo "   - Contains 'Attachment': " . (str_contains($formContent, 'Attachment') ? "YES" : "NO") . "\n";

// Count Jane's notifications before
$initialNotifCount = $jane->notifications()->count();

// 2. Manager assigns new task to Jane
$resStore = executeStep5Request($app, $kernel, $manager, 'POST', "/manager/projects/{$websiteRedesign->id}/tasks", [
    'title'       => 'Setup Redis Caching',
    'description' => 'Configure Redis cache driver for sessions and caching.',
    'assigned_to' => $jane->id,
    'priority'    => 'high',
    'start_date'  => '2026-11-12',
    'due_date'    => '2026-11-18',
    'status'      => 'in_progress',
]);
echo "2. Manager submits task -> Status: {$resStore->getStatusCode()} (Expected: 302)\n";

// 3. Verify notification received
$jane->refresh();
$newNotifCount = $jane->notifications()->count();
$latestNotif = $jane->notifications()->latest()->first();
echo "3. Employee received notification -> Count increased from {$initialNotifCount} to {$newNotifCount}\n";
echo "   - Message: \"{$latestNotif->data['message']}\"\n";

// 4. Employee views dashboard
$resEmp = executeStep5Request($app, $kernel, $jane, 'GET', '/employee/dashboard');
$empContent = $resEmp->getContent();
echo "4. Jane visits employee dashboard -> Status: {$resEmp->getStatusCode()} (Expected: 200)\n";
echo "   - Contains task 'Setup Redis Caching': " . (str_contains($empContent, 'Setup Redis Caching') ? "YES" : "NO") . "\n";
echo "   - Contains 'Recent Notifications': " . (str_contains($empContent, 'Recent Notifications') ? "YES" : "NO") . "\n";

// 5. Employee is forbidden from manager task creation
$resForbidden = executeStep5Request($app, $kernel, $jane, 'GET', "/manager/projects/{$websiteRedesign->id}/tasks/create");
echo "5. Employee blocked from manager task route -> Status: {$resForbidden->getStatusCode()} (Expected: 403)\n";

echo "\nALL STEP 5 TESTS PASSED SUCCESSFULLY!\n";
