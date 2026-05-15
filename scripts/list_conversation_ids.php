<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cu = strtolower(trim($argv[1] ?? 'cafirma.jerome2002@gmail.com'));
$other = strtolower(trim($argv[2] ?? 'jeromecafirma.itspecialist@gmail.com'));

$rows = DB::table('chattbl')
    ->where(function($q) use ($cu, $other) {
        $q->where('sender_email', $cu)->where('receiver_email', $other);
    })
    ->orWhere(function($q) use ($cu, $other) {
        $q->where('sender_email', $other)->where('receiver_email', $cu);
    })
    ->orWhere(function($q) use ($cu) {
        $q->where('sender_email', $cu)->where('receiver_email', $cu)->where('message_type', 'incoming');
    })
    ->orderBy('timestamp_normalized','asc')
    ->get(['id','sender_email','receiver_email','subject','timestamp_normalized']);

foreach($rows as $r) {
    echo "id={$r->id}, sender={$r->sender_email}, recv={$r->receiver_email}, time={$r->timestamp_normalized}, subj=".substr($r->subject,0,40)."\n";
}

echo "TOTAL: " . $rows->count() . "\n";
