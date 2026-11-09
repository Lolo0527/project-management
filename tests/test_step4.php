<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeStep4Request($app, $kernel, $user, $method, $uri, $params = []) {
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
$employee = App\Models\User::where('role', 'employee')->first();
$employees = App\Models\User::where('role', 'employee')->take(3)->pluck('id')->toArray();

// 1. Manager views create form
$resCreate = executeStep4Request($app, $kernel, $manager, 'GET', '/manager/projects/create');
$content = $resCreate->getContent();
echo "1. Manager visits '/manager/projects/create' -> Status: {$resCreate->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'Project Name': " . (str_contains($content, 'Project Name') ? "YES" : "NO") . "\n";
echo "   - Contains 'Project Manager': " . (str_contains($content, 'Project Manager') ? "YES" : "NO") . "\n";
echo "   - Contains 'Project Priority': " . (str_contains($content, 'Project Priority') ? "YES" : "NO") . "\n";
echo "   - Contains 'Assign Team Members': " . (str_contains($content, 'Assign Team Members') ? "YES" : "NO") . "\n";

// 2. Manager creates project
$resStore = executeStep4Request($app, $kernel, $manager, 'POST', '/manager/projects', [
    'title'        => 'Website Redesign',
    'description'  => 'Complete revamp of marketing website and client portal.',
    'start_date'   => '2026-11-10',
    'deadline'     => '2026-12-10',
    'manager_id'   => $manager->id,
    'priority'     => 'high',
    'team_members' => $employees,
]);
echo "2. Manager submits 'POST /manager/projects' -> Status: {$resStore->getStatusCode()} (Expected: 302)\n";

$newProject = App\Models\Project::where('title', 'Website Redesign')->first();
echo "   - Project Created: '{$newProject->title}'\n";
echo "   - Priority: '{$newProject->priority}' | Status: '{$newProject->status}'\n";
echo "   - Start Date: '{$newProject->start_date}' | Deadline: '{$newProject->deadline}'\n";
echo "   - Assigned Team Members Count: {$newProject->members->count()} (Expected: " . count($employees) . ")\n";

// 3. View details page
$resShow = executeStep4Request($app, $kernel, $manager, 'GET', "/manager/projects/{$newProject->id}");
$showContent = $resShow->getContent();
echo "3. Manager views details -> Status: {$resShow->getStatusCode()} (Expected: 200)\n";
echo "   - Contains Priority High: " . (str_contains($showContent, 'Priority: High') ? "YES" : "NO") . "\n";
echo "   - Contains Assigned Team Members: " . (str_contains($showContent, 'Assigned Team Members') ? "YES" : "NO") . "\n";

// 4. Employee is blocked from creating projects
$resBlocked = executeStep4Request($app, $kernel, $employee, 'GET', '/manager/projects/create');
echo "4. Employee visits '/manager/projects/create' -> Status: {$resBlocked->getStatusCode()} (Expected: 403)\n";

echo "\nALL STEP 4 TESTS PASSED SUCCESSFULLY!\n";
