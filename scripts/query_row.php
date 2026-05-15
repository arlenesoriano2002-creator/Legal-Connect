<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$id = $argv[1] ?? null;
if (!$id) { echo "Usage: php query_row.php <id>\n"; exit(1); }
$r = DB::table('chattbl')->where('id', $id)->first();
echo json_encode($r, JSON_PRETTY_PRINT);
