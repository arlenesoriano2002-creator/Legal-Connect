<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EmailReceiverController;

$authEmail = $argv[1] ?? 'cafirma.jerome2002@gmail.com';
$other = $argv[2] ?? 'jeromecafirma.itspecialist@gmail.com';
$sync = $argv[3] ?? '0';

// Find user and login
$user = DB::table('users')->where('email', $authEmail)->first();
if (!$user) {
    echo json_encode(['error' => "User with email {$authEmail} not found"]);
    exit(1);
}

// Login via facade
Auth::loginUsingId($user->id);

// Create request
$request = Request::create('/email/latest/' . $other, 'GET', ['sync' => $sync]);

$controller = app()->make(EmailReceiverController::class);
$response = $controller->getLatestMessage($request, $other);

// If response is instance of Response
if (is_object($response) && method_exists($response, 'getContent')) {
    echo $response->getContent();
} else {
    echo json_encode(['error' => 'Unexpected controller response', 'response' => $response]);
}
