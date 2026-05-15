<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$current = strtolower(trim($argv[1] ?? 'cafirma.jerome2002@gmail.com'));
$other = strtolower(trim($argv[2] ?? 'jeromecafirma.itspecialist@gmail.com'));

$totalInvolving = DB::table('chattbl')
    ->where('sender_email', $current)
    ->orWhere('receiver_email', $current)
    ->orWhere('sender_email', $other)
    ->orWhere('receiver_email', $other)
    ->count();

$conversation = DB::table('chattbl')
    ->where(function($q) use ($current, $other) {
        $q->where('sender_email', $current)->where('receiver_email', $other);
    })
    ->orWhere(function($q) use ($current, $other) {
        $q->where('sender_email', $other)->where('receiver_email', $current);
    })
    ->orWhere(function($q) use ($current) {
        $q->where('sender_email', $current)->where('receiver_email', $current)->where('message_type', 'incoming');
    })
    ->count();

$extra = DB::table('chattbl')
    ->where(function($q) use ($current, $other) {
        $q->where('sender_email', $other)->whereNull('receiver_email');
    })
    ->orWhere(function($q) use ($current, $other) {
        $q->where('receiver_email', $other)->whereNull('sender_email');
    })
    ->count();

echo "totalInvolving={$totalInvolving}\n";
echo "conversation={$conversation}\n";
echo "extraWithNulls={$extra}\n";

$rows = DB::table('chattbl')
    ->where(function($q) use ($current, $other) {
        $q->where('sender_email', $other)->whereNull('receiver_email');
    })
    ->orWhere(function($q) use ($current, $other) {
        $q->where('receiver_email', $other)->whereNull('sender_email');
    })
    ->get();

if ($rows->count() > 0) {
    echo "Rows with null counterparts:\n";
    foreach ($rows as $r) {
        echo "id={$r->id}, sender_email={$r->sender_email}, receiver_email={$r->receiver_email}, message_type={$r->message_type}\n";
    }
}
