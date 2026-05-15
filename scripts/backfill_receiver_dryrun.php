<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$old = 'cafirma.jerome07@gmail.com';
$new = 'cafirma.jerome2002@gmail.com';
$partner = 'jeromecafirma.itspecialist@gmail.com';

// Rows where partner is the other side but receiver is old
$rows1 = DB::select("select id, sender_email, receiver_email, created_at from chattbl where ((sender_email=? and receiver_email=?) or (sender_email=? and receiver_email=?)) and receiver_email = ? order by created_at asc", [$partner, $old, $old, $partner, $old]);

// Rows where partner is the other side but sender is old (incoming direction reversed)
$rows2 = DB::select("select id, sender_email, receiver_email, created_at from chattbl where ((sender_email=? and receiver_email=?) or (sender_email=? and receiver_email=?)) and receiver_email = ? order by created_at asc", [$partner, $new, $new, $partner, $new]);

// Actually rows1 above contains rows where receiver_email = old and participants are partner and old.
$rowsAffected = count($rows1);

echo json_encode([
    'old_account' => $old,
    'new_account' => $new,
    'partner' => $partner,
    'rows_to_update_count' => $rowsAffected,
    'sample_ids' => array_map(function($r){return $r->id;}, $rows1),
    'rows_to_update' => $rows1
], JSON_PRETTY_PRINT);
