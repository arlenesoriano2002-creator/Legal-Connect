<?php
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel app
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$date = $argv[1] ?? '2026-02-04';
$rows = DB::table('cordon_time_slots')->where('date', $date)->orderBy('time_slot')->get();

if ($rows->isEmpty()) {
    echo "No rows for date: $date\n";
    exit(0);
}

foreach ($rows as $r) {
    echo "id={$r->id} date={$r->date} time={$r->time} time_slot={$r->time_slot} slot_number={$r->slot_number} capacity={$r->capacity} color={$r->color} description={$r->description} booked={$r->booked}\n";
}
