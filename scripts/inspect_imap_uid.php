<?php
// IMAP inspection tool disabled. Use Mailjet webhooks.
header('Content-Type: application/json');
echo json_encode(['status' => 'deprecated', 'message' => 'IMAP tools are disabled. Use Mailjet webhooks for inbound mail.']);
exit(0);


$uid = intval($argv[1] ?? 0);
if (!$uid) { echo "Usage: php inspect_imap_uid.php <uid>\n"; exit(1); }
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
    $folder = $client->getFolder('INBOX');
    $messages = $folder->query()->uid($uid)->get();
    if (!count($messages)) { echo "No message with UID {$uid}\n"; exit(0); }
    $m = $messages[0];
    echo "UID: {$uid}\n";
    echo "From: "; try{ echo ($m->getFrom()[0]->personal ?? '') . ' <' . ($m->getFrom()[0]->mail ?? '') . '>'; }catch(Throwable $e){ echo 'n/a'; }
    echo "\nTo: "; try{ echo ($m->getTo()[0]->mail ?? 'n/a'); }catch(Throwable $e){ echo 'n/a'; }
    echo "\nDate: " . (string)$m->getDate() . "\n";
    echo "Subject: " . ($m->getSubject() ?? '') . "\n";
    echo "Message-ID: " . ($m->getMessageId() ?? '') . "\n";
    echo "Preview: " . substr(trim(strip_tags($m->getTextBody() ?: $m->getHTMLBody() ?? '')),0,400) . "\n";
    echo "Headers:\n";
    $hdrs = (array)$m->getHeaders();
    foreach($hdrs as $k=>$v){
        echo "$k: ";
        if (is_array($v)) echo json_encode($v);
        else echo (string)$v;
        echo "\n";
    }
    $client->disconnect();
} catch (Throwable $e){
    echo "Error: " . $e->getMessage() . "\n";
}
