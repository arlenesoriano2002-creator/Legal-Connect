<?php
// IMAP script disabled.
header('Content-Type: application/json');
echo json_encode(['status' => 'deprecated', 'message' => 'IMAP scripts are disabled. Use Mailjet webhooks for inbound mail.']);
exit(0);

if (!$sender) {
    echo json_encode(['error' => 'Provide sender email as first argument']);
    exit(1);
}
$days = intval($argv[2] ?? 3);
try {
    $cm = new ClientManager();
    $client = $cm->make([
        'host' => env('IMAP_HOST'),
        'port' => env('IMAP_PORT'),
        'encryption' => env('IMAP_ENCRYPTION'),
        'validate_cert' => true,
        'username' => env('IMAP_USERNAME'),
        'password' => env('IMAP_PASSWORD'),
        'protocol' => 'imap'
    ]);
    $client->connect();
    $inbox = $client->getFolder('INBOX');

    $messages = $inbox->query()->from($sender)->since(Carbon::now()->subDays($days))->limit(200)->get();
    $imported = 0;
    $inspected = 0;
    $errors = [];
    foreach ($messages as $m) {
        $inspected++;
        try {
            $messageId = $m->getMessageId() ?: null;
            $from = strtolower(trim(optional($m->getFrom()[0])->mail ?? ''));
            $to = strtolower(trim($m->getTo()[0]->mail ?? env('IMAP_USERNAME')));
            $body = $m->getTextBody() ?: strip_tags($m->getHTMLBody() ?? '');
            $manila = Carbon::now('Asia/Manila');
            $ts = $manila->format('Y-m-d H:i:s');

            $exists = $messageId ? DB::table('chattbl')->where('message_id', $messageId)->exists() : false;
            if ($exists) continue;

            DB::table('chattbl')->insert([
                'message_id' => $messageId,
                // Use NULL for sender_id to avoid foreign key issues/triggers
                'sender_id' => null,
                'sender_email' => $from,
                'sender_name' => $m->getFrom()[0]->personal ?? $from,
                'receiver_id' => DB::table('users')->where('email', $to)->value('id') ?? null,
                'receiver_email' => $to,
                'subject' => $m->getSubject() ?? 'No Subject',
                'message' => substr($body, 0, 2000),
                'sender_role' => 'email',
                'message_type' => 'incoming',
                'created_at' => $manila,
                'updated_at' => $manila,
                'timestamp_normalized' => $ts,
            ]);
            $imported++;
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
            continue;
        }
    }
    $client->disconnect();
    echo json_encode(['inspected' => $inspected, 'imported' => $imported, 'errors' => $errors], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
