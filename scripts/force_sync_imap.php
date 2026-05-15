<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailChatService;

$imap = strtolower(trim(getenv('IMAP_USERNAME') ?: 'cafirma.jerome2002@gmail.com'));

try {
    // Force re-import recent messages even if message_id exists
    // IMAP sync deprecated. Mailjet webhooks are the supported inbound mechanism.
    echo json_encode(['status'=>'deprecated','message'=>'IMAP sync deprecated. Use Mailjet webhooks instead.']);
} catch (\Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
