<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$imap = strtolower(trim(getenv('IMAP_USERNAME') ?: 'cafirma.jerome2002@gmail.com'));
$rows = DB::table('chattbl')->where('receiver_email', $imap)->orderBy('id','desc')->limit(10)->get();
echo json_encode(['imap'=>$imap,'count'=>count($rows),'rows'=>$rows], JSON_PRETTY_PRINT);
