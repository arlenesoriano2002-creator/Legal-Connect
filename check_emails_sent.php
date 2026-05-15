<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$messages = DB::table('chattbl')
    ->where('receiver_email', 'jeromecafirma472@gmail.com')
    ->latest()
    ->limit(10)
    ->get(['id', 'sender_email', 'receiver_email', 'subject', 'message_type', 'created_at']);

echo "Total messages to jeromecafirma472@gmail.com: " . count($messages) . "\n\n";
foreach($messages as $msg) {
    echo "ID: {$msg->id}, Type: {$msg->message_type}, Subject: {$msg->subject}, Created: {$msg->created_at}\n";
}
?>
