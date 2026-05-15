<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$old = 'cafirma.jerome07@gmail.com';
$new = 'cafirma.jerome2002@gmail.com';

$newUserId = DB::table('users')->where('email', $new)->value('id');

$rows = DB::table('chattbl')->where('receiver_email', $old)->orderBy('created_at','asc')->get();

$updated = [];
$skipped = [];
$errors = [];

DB::beginTransaction();
try {
    foreach ($rows as $r) {
        $messageId = trim($r->message_id ?? '');
        // If message_id present and identical message already exists for new receiver, skip
        if (!empty($messageId)) {
            $exists = DB::table('chattbl')->where('message_id', $messageId)->where('receiver_email', $new)->exists();
            if ($exists) {
                $skipped[] = ['id'=>$r->id,'reason'=>'duplicate_message_id','message_id'=>$messageId];
                continue;
            }
        }

        $updateData = ['receiver_email' => $new];
        if ($newUserId) {
            $updateData['receiver_id'] = $newUserId;
        }

        // Update the row
        DB::table('chattbl')->where('id', $r->id)->update($updateData);
        $updated[] = ['id'=>$r->id,'old_receiver'=>$old,'new_receiver'=>$new];
    }
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    $errors[] = $e->getMessage();
}

echo json_encode([
    'old' => $old,
    'new' => $new,
    'new_user_id' => $newUserId,
    'rows_found' => count($rows),
    'updated_count' => count($updated),
    'skipped_count' => count($skipped),
    'skipped_sample' => array_slice($skipped,0,10),
    'updated_sample' => array_slice($updated,0,10),
    'errors' => $errors
], JSON_PRETTY_PRINT);
