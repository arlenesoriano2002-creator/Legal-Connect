<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sender = $argv[1] ?? 'jeromecafirma.itspecialist@gmail.com';
$needle = $argv[2] ?? 'succes';

$results = DB::table('chattbl')
    ->where('sender_email', $sender)
    ->where(function($q) use ($needle) {
        $q->where('message', 'like', "%$needle%")
          ->orWhere('subject', 'like', "%$needle%");
    })
    ->orderBy('timestamp_normalized', 'desc')
    ->get();

echo json_encode(['count' => $results->count(), 'rows' => $results], JSON_PRETTY_PRINT);
