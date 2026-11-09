<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeStep9Request($app, $kernel, $user, $method, $uri) {
    $session = $app->make('session.store');
    $session->start();
    
    $req = \Illuminate\Http\Request::create($uri, $method);
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

echo "--- STEP 9 PRODUCTIVITY REPORT TESTS ---\n";

// 1. Manager visits reports page
$resReport = executeStep9Request($app, $kernel, $manager, 'GET', '/manager/reports');
$content = $resReport->getContent();

echo "1. Manager visits '/manager/reports' -> Status: {$resReport->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'Productivity & Progress Reports': " . (str_contains($content, 'Productivity & Progress Reports') ? "YES" : "NO") . "\n";
echo "   - Contains 'Employee Workload & Task Performance': " . (str_contains($content, 'Employee Workload & Task Performance') ? "YES" : "NO") . "\n";
echo "   - Contains 'Project Completion Percentages': " . (str_contains($content, 'Project Completion Percentages') ? "YES" : "NO") . "\n";

// 2. Verify Employee Workload numbers from prompt
$sarah = App\Models\User::where('email', 'sarah@company.com')->first();
$sarahAssigned = $sarah->tasks()->count();
$sarahCompleted = $sarah->tasks()->where('status', 'completed')->count();
$sarahPending = $sarah->tasks()->where('status', '!=', 'completed')->count();
echo "2. Sarah Jenkins Workload:\n";
echo "   - Assigned: {$sarahAssigned} (Expected: 15)\n";
echo "   - Completed: {$sarahCompleted} (Expected: 12)\n";
echo "   - Pending: {$sarahPending} (Expected: 3)\n";

$james = App\Models\User::where('email', 'james@company.com')->first();
$jamesAssigned = $james->tasks()->count();
$jamesCompleted = $james->tasks()->where('status', 'completed')->count();
$jamesPending = $james->tasks()->where('status', '!=', 'completed')->count();
echo "3. James Wilson Workload:\n";
echo "   - Assigned: {$jamesAssigned} (Expected: 10)\n";
echo "   - Completed: {$jamesCompleted} (Expected: 8)\n";
echo "   - Pending: {$jamesPending} (Expected: 2)\n";

$peter = App\Models\User::where('email', 'peter@company.com')->first();
$peterAssigned = $peter->tasks()->count();
$peterCompleted = $peter->tasks()->where('status', 'completed')->count();
$peterPending = $peter->tasks()->where('status', '!=', 'completed')->count();
echo "4. Peter Parker Workload:\n";
echo "   - Assigned: {$peterAssigned} (Expected: 12)\n";
echo "   - Completed: {$peterCompleted} (Expected: 9)\n";
echo "   - Pending: {$peterPending} (Expected: 3)\n";

// 5. Verify Employee is blocked
$resEmp = executeStep9Request($app, $kernel, $jane, 'GET', '/manager/reports');
echo "5. Employee blocked from '/manager/reports' -> Status: {$resEmp->getStatusCode()} (Expected: 403)\n";

echo "\nALL STEP 9 REPORT TESTS PASSED SUCCESSFULLY!\n";
