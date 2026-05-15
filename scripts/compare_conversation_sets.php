<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$a = 'cafirma.jerome2002@gmail.com';
$b = 'jeromecafirma.itspecialist@gmail.com';

// Set A: controller-style (ordered pair)
$set1 = DB::select("select id from chattbl where (sender_email = ? and receiver_email = ?) or (sender_email = ? and receiver_email = ?) order by timestamp_normalized asc", [$a,$b,$b,$a]);
$ids1 = array_map(function($r){return $r->id;}, $set1);

// Set 2: looser set where both emails appear in either column
$set2 = DB::select("select id from chattbl where sender_email in (?,?) and receiver_email in (?,?) order by timestamp_normalized asc", [$a,$b,$a,$b]);
$ids2 = array_map(function($r){return $r->id;}, $set2);

// Set 3: also include rows where one column is NULL but other matches? We'll also find rows where message_type='incoming' and (sender_email = b and (receiver_email is null or receiver_email not in a,b))
$set3 = DB::select("select id,sender_email,receiver_email from chattbl where (sender_email = ? and (receiver_email is null or receiver_email not in (?,?))) or (receiver_email = ? and (sender_email is null or sender_email not in (?,?)))", [$b,$a,$b,$a,$a,$b]);

echo json_encode([
    'controller_count' => count($ids1),
    'controller_ids' => $ids1,
    'inboth_count' => count($ids2),
    'inboth_ids' => $ids2,
    'others_count' => count($set3),
    'others' => $set3
], JSON_PRETTY_PRINT);
