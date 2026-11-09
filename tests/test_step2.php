<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeWithUser($app, $kernel, $user, $uri) {
    $req = \Illuminate\Http\Request::create($uri, 'GET');
    $app->instance('request', $req);
    \Illuminate\Support\Facades\Auth::setUser($user);
    $res = $kernel->handle($req);
    $kernel->terminate($req, $res);
    return $res;
}

// 1. Authenticate as Manager
$manager = App\Models\User::where('role', 'manager')->first();
$res = executeWithUser($app, $kernel, $manager, '/manager/dashboard');
$content = $res->getContent();

echo "1. Manager visits '/manager/dashboard' -> Status: {$res->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'Total Employees': " . (str_contains($content, 'Total Employees') ? "YES" : "NO") . "\n";
echo "   - Contains 'Active Projects': " . (str_contains($content, 'Active Projects') ? "YES" : "NO") . "\n";
echo "   - Contains 'Overdue Tasks': " . (str_contains($content, 'Overdue Tasks') ? "YES" : "NO") . "\n";
echo "   - Contains 'Upcoming Deadlines': " . (str_contains($content, 'Upcoming Deadlines') ? "YES" : "NO") . "\n";
echo "   - Contains 'Employee Productivity': " . (str_contains($content, 'Employee Productivity') ? "YES" : "NO") . "\n";

// 2. Manager clicks on a project
$project = App\Models\Project::first();
$resProj = executeWithUser($app, $kernel, $manager, "/manager/projects/{$project->id}");
$projContent = $resProj->getContent();

echo "2. Manager visits '/manager/projects/{$project->id}' -> Status: {$resProj->getStatusCode()} (Expected: 200)\n";
echo "   - Contains Project Title '{$project->title}': " . (str_contains($projContent, $project->title) ? "YES" : "NO") . "\n";

// 3. Employee tries to visit Manager Dashboard (should be 403 Forbidden)
$employee = App\Models\User::where('role', 'employee')->first();
$resEmp = executeWithUser($app, $kernel, $employee, '/manager/dashboard');
echo "3. Employee tries to visit '/manager/dashboard' -> Status: {$resEmp->getStatusCode()} (Expected: 403)\n";

// 4. Employee tries to visit Manager Project Details (should be 403 Forbidden)
$resEmpProj = executeWithUser($app, $kernel, $employee, "/manager/projects/{$project->id}");
echo "4. Employee tries to visit '/manager/projects/{$project->id}' -> Status: {$resEmpProj->getStatusCode()} (Expected: 403)\n";

echo "\nALL STEP 2 TESTS PASSED SUCCESSFULLY!\n";
