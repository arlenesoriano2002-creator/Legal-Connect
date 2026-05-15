<?php
// IMAP search tool disabled.
header('Content-Type: application/json');
echo json_encode(['status' => 'deprecated', 'message' => 'IMAP utilities are disabled. Use Mailjet webhooks.']);
exit(0);


$term = $argv[1] ?? 'test received';
$hours = intval($argv[2] ?? 6);
$since = Carbon::now()->subHours($hours);
try{
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
    $folders = $client->getFolders();
    echo "Searching folders since {$since} for term: {$term}\n";
    $found = [];
    foreach($folders as $folder){
        $fname = $folder->name;
        try{
            $msgs = $folder->query()->since($since)->limit(200)->get();
        } catch(\Throwable $e){
            // skip
            continue;
        }
        foreach($msgs as $m){
            $body = ($m->getTextBody() ?: strip_tags($m->getHTMLBody() ?? ''));
            if (stripos($body, $term) !== false || stripos($m->getSubject() ?? '', $term) !== false) {
                $from = $m->getFrom()[0] ?? null;
                $fromMail = $from->mail ?? 'unknown';
                $fromName = $from->personal ?? '';
                $date = (string)$m->getDate();
                $mid = $m->getMessageId();
                $found[] = [
                    'folder' => $fname,
                    'uid' => $m->getUid(),
                    'date' => $date,
                    'from' => $fromName . ' <'.$fromMail.'>',
                    'subject' => $m->getSubject(),
                    'message_id' => $mid,
                    'snippet' => substr(trim(preg_replace('/\s+/', ' ', $body)),0,200)
                ];
            }
        }
    }
    echo json_encode(['found_count' => count($found), 'results' => $found], JSON_PRETTY_PRINT);
    $client->disconnect();
} catch(\Throwable $e){
    echo json_encode(['error' => $e->getMessage()]);
}
