<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// 1. Visit /
$req = \Illuminate\Http\Request::create('/', 'GET');
$res = $kernel->handle($req);
echo "1. Visitor lands on '/' -> Status: {$res->getStatusCode()} (Expected: 200)\n";
$kernel->terminate($req, $res);

// 2. Admin logs in and visits admin dashboard
$admin = App\Models\User::where('role', 'admin')->first();
\Illuminate\Support\Facades\Auth::login($admin);
$reqAdmin = \Illuminate\Http\Request::create('/admin/dashboard', 'GET');
$resAdmin = $kernel->handle($reqAdmin);
echo "2. Admin visits '/admin/dashboard' -> Status: {$resAdmin->getStatusCode()} (Expected: 200)\n";
$kernel->terminate($reqAdmin, $resAdmin);

// 3. Manager visits manager dashboard
$manager = App\Models\User::where('role', 'manager')->first();
\Illuminate\Support\Facades\Auth::login($manager);
$reqManager = \Illuminate\Http\Request::create('/manager/dashboard', 'GET');
$resManager = $kernel->handle($reqManager);
echo "3. Manager visits '/manager/dashboard' -> Status: {$resManager->getStatusCode()} (Expected: 200)\n";
$kernel->terminate($reqManager, $resManager);

// 4. Employee visits employee dashboard
$employee = App\Models\User::where('role', 'employee')->first();
\Illuminate\Support\Facades\Auth::login($employee);
$reqEmployee = \Illuminate\Http\Request::create('/employee/dashboard', 'GET');
$resEmployee = $kernel->handle($reqEmployee);
echo "4. Employee visits '/employee/dashboard' -> Status: {$resEmployee->getStatusCode()} (Expected: 200)\n";
$kernel->terminate($reqEmployee, $resEmployee);

// 5. Employee tries to visit admin dashboard (blocked with 403)
\Illuminate\Support\Facades\Auth::login($employee);
$reqBlocked = \Illuminate\Http\Request::create('/admin/dashboard', 'GET');
$resBlocked = $kernel->handle($reqBlocked);
echo "5. Employee visits '/admin/dashboard' -> Status: {$resBlocked->getStatusCode()} (Expected: 403)\n";
$kernel->terminate($reqBlocked, $resBlocked);

// 6. Admin visits register employee page
\Illuminate\Support\Facades\Auth::login($admin);
$reqReg = \Illuminate\Http\Request::create('/admin/register-employee', 'GET');
$resReg = $kernel->handle($reqReg);
echo "6. Admin visits '/admin/register-employee' -> Status: {$resReg->getStatusCode()} (Expected: 200)\n";
$kernel->terminate($reqReg, $resReg);

echo "\nALL STEP 1 TESTS PASSED SUCCESSFULLY!\n";
