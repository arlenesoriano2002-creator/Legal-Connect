<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailChatService;
use Illuminate\Support\Facades\DB;

$imap = env('IMAP_USERNAME');
if (!$imap) {
    echo "No IMAP_USERNAME set in env\n";
    exit(1);
}

echo "Triggering syncInboxFromGmail for {$imap} (force=true)\n";
// IMAP sync deprecated; Mailjet webhooks should be used instead.
// EmailChatService::syncInboxFromGmail($imap, true); // removed

$count = DB::table('chattbl')->where(function($q) use ($imap) {
    $q->where('sender_email', $imap)->orWhere('receiver_email', $imap);
})->count();

echo "Conversation rows involving {$imap}: {$count}\n";