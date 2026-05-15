<?php
// IMAP list utility disabled.
header('Content-Type: application/json');
echo json_encode(['status' => 'deprecated', 'message' => 'IMAP utilities are disabled. Use Mailjet webhooks.']);
exit(0);

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
    $messages = $inbox->query()->limit($limit)->all()->get();
    $out = [];
    foreach ($messages as $m) {
        $from = null;
        $to = null;
        try { $from = $m->getFrom()[0]->mail ?? null; } catch (\Throwable $_) {}
        try { $to = $m->getTo()[0]->mail ?? null; } catch (\Throwable $_) {}

        $out[] = [
            'uid' => $m->getUid(),
            'message_id' => $m->getMessageId(),
            'subject' => $m->getSubject(),
            'from' => $from,
            'to' => $to,
            'date' => $m->getDate(),
            'preview' => substr(trim(strip_tags($m->getTextBody() ?? $m->getHTMLBody() ?? '')),0,200),
        ];
    }
    echo json_encode(['count' => count($out), 'messages' => $out], JSON_PRETTY_PRINT);
    $client->disconnect();
} catch (\Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
