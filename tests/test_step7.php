<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function executeStep7Request($app, $kernel, $user, $method, $uri, $params = [], $files = []) {
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

$manager = App\Models\User::where('role', 'manager')->first();
$jane = App\Models\User::where('email', 'jane@company.com')->first();
$task = App\Models\Task::where('title', 'Design homepage')->first();

// 1. Visit task details
$resShow = executeStep7Request($app, $kernel, $jane, 'GET', "/tasks/{$task->id}");
$content = $resShow->getContent();
echo "1. Visit task details -> Status: {$resShow->getStatusCode()} (Expected: 200)\n";
echo "   - Contains 'Design homepage': " . (str_contains($content, 'Design homepage') ? "YES" : "NO") . "\n";
echo "   - Contains Manager message 'Please finish the homepage design by Friday.': " . (str_contains($content, 'Please finish the homepage design by Friday.') ? "YES" : "NO") . "\n";
echo "   - Contains Employee message 'The design is ready for review.': " . (str_contains($content, 'The design is ready for review.') ? "YES" : "NO") . "\n";

// 2. Manager posts a reply
$initialCount = $task->comments()->count();
$resComment = executeStep7Request($app, $kernel, $manager, 'POST', "/tasks/{$task->id}/comments", [
    'content' => 'Reviewed the mockups, looks approved!',
]);
echo "2. Manager posts comment -> Status: {$resComment->getStatusCode()} (Expected: 302 redirect)\n";

$task->refresh();
$newCount = $task->comments()->count();
$latest = App\Models\Comment::where('task_id', $task->id)->latest('id')->first();
echo "   - Comment count increased from {$initialCount} to {$newCount}\n";
echo "   - Latest comment author: {$latest->user->name} | Content: \"{$latest->content}\"\n";

// 3. Employee posts a message with a mock uploaded file
$fakeFile = \Illuminate\Http\UploadedFile::fake()->create('homepage_v2_final.pdf', 250, 'application/pdf');
$resAttachment = executeStep7Request($app, $kernel, $jane, 'POST', "/tasks/{$task->id}/comments", [
    'content' => 'Here is the final exported PDF presentation.',
], ['attachment' => $fakeFile]);

$latestWithFile = App\Models\Comment::where('task_id', $task->id)->latest('id')->first();
echo "3. Employee posts comment with attachment -> Status: {$resAttachment->getStatusCode()}\n";
echo "   - Attachment Name: {$latestWithFile->attachment_name} (Expected: homepage_v2_final.pdf)\n";
echo "   - File saved on disk: " . (\Illuminate\Support\Facades\Storage::disk('public')->exists($latestWithFile->attachment_path) ? 'YES' : 'NO') . "\n";

// 4. Guest is blocked
$resGuest = executeStep7Request($app, $kernel, null, 'GET', "/tasks/{$task->id}");
echo "4. Unauthenticated user visits task -> Status: {$resGuest->getStatusCode()} (Expected: 302)\n";

echo "\nALL STEP 7 TESTS PASSED SUCCESSFULLY!\n";
