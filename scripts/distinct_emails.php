<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pattern = $argv[1] ?? 'jeromecafirma.itspecialist@gmail.com';
$pattern = trim($pattern);

$senders = DB::table('chattbl')->select('sender_email')->where('sender_email','like',"%$pattern%" )->distinct()->get()->pluck('sender_email');
$receivers = DB::table('chattbl')->select('receiver_email')->where('receiver_email','like',"%$pattern%" )->distinct()->get()->pluck('receiver_email');

echo "Sender variants:\n";
foreach($senders as $s) echo "- [$s]\n";

echo "Receiver variants:\n";
foreach($receivers as $r) echo "- [$r]\n";
