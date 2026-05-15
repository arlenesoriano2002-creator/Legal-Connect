<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\MailjetService;
use Carbon\Carbon;

$mailjet = new MailjetService();

// Send a test email
$toList = ['jeromecafirma.itspecialist@gmail.com'];
$subject = 'System Test - ' . Carbon::now()->format('Y-m-d H:i:s');
$message = 'This is a system test email to verify Mailjet integration is working correctly.';

echo "Sending test email...\n";
echo "To: " . implode(', ', $toList) . "\n";
echo "Subject: $subject\n";

$result = $mailjet->sendMessage($toList, $subject, $message, "<p>$message</p>");

echo "\n✅ Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

if ($result['success']) {
    echo "\n✅ EMAIL SENT SUCCESSFULLY!\n";
    echo "Message IDs: " . json_encode($result['message_ids']) . "\n";
} else {
    echo "\n❌ EMAIL SEND FAILED!\n";
    echo "Error: " . ($result['error'] ?? 'Unknown error') . "\n";
}
?>
