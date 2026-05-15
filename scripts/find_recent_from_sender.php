<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sender = $argv[1] ?? 'jeromecafirma.itspecialist@gmail.com';
$minutes = intval($argv[2] ?? 120);

$threshold = \Carbon\Carbon::now('Asia/Manila')->subMinutes($minutes)->format('Y-m-d H:i:s');

$results = DB::table('chattbl')
    ->where('sender_email', $sender)
    ->where('timestamp_normalized', '>=', $threshold)
    ->orderBy('timestamp_normalized', 'desc')
    ->get();

echo json_encode(['since' => $threshold, 'count' => $results->count(), 'rows' => $results], JSON_PRETTY_PRINT);
