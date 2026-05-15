<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('chattbl')
    ->where('message_type', 'incoming')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

echo json_encode($rows->toArray());
