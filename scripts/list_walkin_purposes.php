<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$purposes = DB::table('diffun_walkins')->select('purpose')->distinct()->limit(20)->pluck('purpose')->toArray();
print_r($purposes);
