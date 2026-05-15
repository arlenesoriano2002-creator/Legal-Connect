<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$currentUserEmail = $argv[1] ?? 'cafirma.jerome2002@gmail.com';
$other = $argv[2] ?? 'jeromecafirma.itspecialist@gmail.com';
$currentUserEmail = strtolower(trim($currentUserEmail));
$other = strtolower(trim($other));

$count = DB::table('chattbl')
    ->where(function($q) use ($currentUserEmail, $other) {
        $q->where('sender_email', $currentUserEmail)
          ->where('receiver_email', $other);
    })
    ->orWhere(function($q) use ($currentUserEmail, $other) {
        $q->where('sender_email', $other)
          ->where('receiver_email', $currentUserEmail);
    })
    ->orWhere(function($q) use ($currentUserEmail) {
        $q->where('sender_email', $currentUserEmail)
          ->where('receiver_email', $currentUserEmail)
          ->where('message_type', 'incoming');
    })
    ->count();

echo $count . PHP_EOL;
