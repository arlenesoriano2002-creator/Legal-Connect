<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$rows = DB::select("select id,sender_email,receiver_email,created_at from chattbl where (sender_email like 'jerome%' or receiver_email like 'jerome%') and receiver_email <> 'cafirma.jerome2002@gmail.com' order by created_at desc limit 50");

echo json_encode(['count'=>count($rows),'rows'=>$rows]);
