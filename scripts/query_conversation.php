<?php
// Quick script to dump conversation between two emails
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cur = 'cafirma.jerome2002@gmail.com';
$other = 'jeromecafirma.itspecialist@gmail.com';

$rows = DB::select("select id,sender_email,receiver_email,message_type,timestamp_normalized from chattbl where (sender_email='".$cur."' and receiver_email='".$other."') or (sender_email='".$other."' and receiver_email='".$cur."') order by timestamp_normalized asc");

echo json_encode(['count'=>count($rows),'ids'=>array_map(function($r){return $r->id;}, $rows),'rows'=>$rows]);
