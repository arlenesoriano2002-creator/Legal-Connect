<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sender = $argv[1] ?? 'jeromecafirma.itspecialist@gmail.com';
$limit = intval($argv[2] ?? 50);

$rows = DB::table('chattbl')
    ->where('sender_email', $sender)
    ->orderBy('id', 'desc')
    ->limit($limit)
    ->get();

echo "Latest {$limit} rows for sender: {$sender}\n";
foreach ($rows as $r) {
    echo "id={$r->id}, sender_email={$r->sender_email}, receiver_email={$r->receiver_email}, message_type={$r->message_type}, created_at={$r->created_at}, timestamp_normalized={$r->timestamp_normalized}, message_id=".($r->message_id ?? 'NULL')."\n";
}

$receiver = $argv[3] ?? 'cafirma.jerome2002@gmail.com';
$rows2 = DB::table('chattbl')
    ->where('receiver_email', $receiver)
    ->orderBy('id', 'desc')
    ->limit($limit)
    ->get();

echo "\nLatest {$limit} rows for receiver: {$receiver}\n";
foreach ($rows2 as $r) {
    echo "id={$r->id}, sender_email={$r->sender_email}, receiver_email={$r->receiver_email}, message_type={$r->message_type}, created_at={$r->created_at}, timestamp_normalized={$r->timestamp_normalized}, message_id=".($r->message_id ?? 'NULL')."\n";
}
