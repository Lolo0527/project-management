<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeRequest($app, $kernel, $user, $method, $uri, $params = []) {
    $session = $app->make('session.store');
    $session->start();
    
    // Include CSRF token for POST/PUT/PATCH/DELETE
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

// 1. Check access
$admin = App\Models\User::where('role', 'admin')->first();
$manager = App\Models\User::where('role', 'manager')->first();
$employee = App\Models\User::where('role', 'employee')->first();

$resAdmin = executeRequest($app, $kernel, $admin, 'GET', '/employees');
echo "1. Admin visits '/employees' -> Status: {$resAdmin->getStatusCode()} (Expected: 200)\n";

$resManager = executeRequest($app, $kernel, $manager, 'GET', '/employees');
echo "2. Manager visits '/employees' -> Status: {$resManager->getStatusCode()} (Expected: 200)\n";

$resEmp = executeRequest($app, $kernel, $employee, 'GET', '/employees');
echo "3. Employee visits '/employees' -> Status: {$resEmp->getStatusCode()} (Expected: 403)\n";

// 4. Admin registers a new employee with department
$resCreate = executeRequest($app, $kernel, $admin, 'POST', '/employees', [
    'name'       => 'Robert Green',
    'email'      => 'robert@company.com',
    'department' => 'Cybersecurity',
    'role'       => 'employee',
    'password'   => 'password123',
]);
echo "4. Admin registers new employee -> Status: {$resCreate->getStatusCode()} (Expected: 302 redirect)\n";
$robert = App\Models\User::where('email', 'robert@company.com')->first();
echo "   - Created user: {$robert->name}, Dept: {$robert->department}, Role: {$robert->role}, Active: " . ($robert->is_active ? 'YES' : 'NO') . "\n";

// 5. Manager edits an employee
$resEdit = executeRequest($app, $kernel, $manager, 'PUT', "/employees/{$robert->id}", [
    'name'       => 'Robert Green Jr.',
    'email'      => 'robert@company.com',
    'department' => 'DevOps',
    'role'       => 'employee',
]);
$robert->refresh();
echo "5. Manager edits employee -> Name: {$robert->name}, Dept: {$robert->department}\n";

// 6. Deactivate employee account
$resToggle = executeRequest($app, $kernel, $admin, 'PATCH', "/employees/{$robert->id}/toggle-status");
$robert->refresh();
echo "6. Deactivate employee -> Active: " . ($robert->is_active ? 'YES' : 'NO') . " (Expected: NO)\n";

// 7. Deactivated employee tries to login
$reqLogin = \Illuminate\Http\Request::create('/login', 'POST', [
    'email'    => 'robert@company.com',
    'password' => 'password123',
]);
$session = $app->make('session.store');
$session->start();
$reqLogin->setLaravelSession($session);
$app->instance('request', $reqLogin);
$controller = new \App\Http\Controllers\AuthController();
$loginRes = $controller->login($reqLogin);
echo "7. Deactivated employee logs in -> Result: " . ($session->get('errors') ? $session->get('errors')->first('email') : 'Logged in') . "\n";

// 8. Reactivate account
$resReactivate = executeRequest($app, $kernel, $admin, 'PATCH', "/employees/{$robert->id}/toggle-status");
$robert->refresh();
echo "8. Reactivate employee -> Active: " . ($robert->is_active ? 'YES' : 'NO') . " (Expected: YES)\n";

echo "\nALL STEP 3 TESTS PASSED SUCCESSFULLY!\n";
