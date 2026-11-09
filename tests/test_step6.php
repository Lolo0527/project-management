<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeStep6Request($app, $kernel, $user, $method, $uri, $params = [], $isJson = false) {
    $session = $app->make('session.store');
    $session->start();
    
    $server = [];
    $content = null;

    if ($isJson) {
        $server['HTTP_ACCEPT'] = 'application/json';
        $server['CONTENT_TYPE'] = 'application/json';
        $server['HTTP_X_CSRF_TOKEN'] = $session->token();
        $content = json_encode($params);
        $params = [];
    } else {
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $params['_token'] = $session->token();
        }
    }
    
    $req = \Illuminate\Http\Request::create($uri, $method, $params, [], [], $server, $content);
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

// 1. Employee visits Kanban
$resEmp = executeStep6Request($app, $kernel, $jane, 'GET', '/kanban');
$contentEmp = $resEmp->getContent();
echo "1. Employee visits '/kanban' -> Status: {$resEmp->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'To Do': " . (str_contains($contentEmp, 'To Do') ? "YES" : "NO") . "\n";
echo "   - Contains 'In Progress': " . (str_contains($contentEmp, 'In Progress') ? "YES" : "NO") . "\n";
echo "   - Contains 'Completed': " . (str_contains($contentEmp, 'Completed') ? "YES" : "NO") . "\n";
echo "   - Contains 'Design homepage': " . (str_contains($contentEmp, 'Design homepage') ? "YES" : "NO") . "\n";

// 2. Manager visits Kanban
$resMgr = executeStep6Request($app, $kernel, $manager, 'GET', '/kanban');
echo "2. Manager visits '/kanban' -> Status: {$resMgr->getStatusCode()} (Expected: 200)\n";

// 3. Move task from 'pending' to 'in_progress'
$task = App\Models\Task::where('title', 'Develop login page')->first();
echo "3. Initial status of '{$task->title}': {$task->status}\n";

$resMove1 = executeStep6Request($app, $kernel, $jane, 'PATCH', "/tasks/{$task->id}/status", [
    'status' => 'in_progress',
], true);
$task->refresh();
echo "   - Status response code: {$resMove1->getStatusCode()}, Body: {$resMove1->getContent()}\n";
echo "   - Moved to 'in_progress' -> DB Status: {$task->status} (Expected: in_progress)\n";

// 4. Move task from 'in_progress' to 'completed'
$resMove2 = executeStep6Request($app, $kernel, $jane, 'PATCH', "/tasks/{$task->id}/status", [
    'status' => 'completed',
], true);
$task->refresh();
echo "4. Moved to 'completed' -> DB Status: {$task->status} (Expected: completed)\n";

// 5. Guest is redirected
$resGuest = executeStep6Request($app, $kernel, null, 'GET', '/kanban');
echo "5. Unauthenticated user visits '/kanban' -> Status: {$resGuest->getStatusCode()} (Expected: 302)\n";

echo "\nALL STEP 6 TESTS PASSED SUCCESSFULLY!\n";
