<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$since = date('Y-m-d H:i:s', strtotime('-3 days'));
$rows = DB::select("select id,sender_email,sender_name,receiver_email,subject,message_type,created_at,timestamp_normalized from chattbl where sender_email like 'jeromecafirma%' and created_at >= ? order by created_at desc", [$since]);

echo json_encode(['count'=>count($rows),'rows'=>$rows]);
