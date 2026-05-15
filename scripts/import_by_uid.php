<?php
// IMAP import has been disabled.
// Please use Mailjet webhooks for inbound messages. This script intentionally exits to
// ensure IMAP is not used anywhere in the system.
header('Content-Type: application/json');
echo json_encode(['status' => 'deprecated', 'message' => 'IMAP import deprecated. Use Mailjet webhooks instead.']);
exit(0);

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
    $folder = $client->getFolder($folderName);
    // Webklex v3: use query to fetch by uid
    $messages = $folder->query()->uid($uid)->get();
    $message = count($messages) ? $messages[0] : null;
    if (!$message) { echo json_encode(['error'=>'Message not found']); exit(1); }

    $messageId = $message->getMessageId() ?: null;
    $from = strtolower(trim($message->getFrom()[0]->mail ?? ''));
    $to = strtolower(trim($message->getTo()[0]->mail ?? env('IMAP_USERNAME')));
    $body = $message->getTextBody() ?: strip_tags($message->getHTMLBody() ?? '');
    $manila = Carbon::now('Asia/Manila');
    $ts = $manila->format('Y-m-d H:i:s');

    $exists = false;
    if ($messageId) {
        $exists = DB::table('chattbl')->where('message_id', $messageId)->exists();
    } else {
        // Try conservative existence check: sender+to+created_at within 2 minutes
        $msgDate = $message->getDate() ? (string)$message->getDate() : null;
        if ($msgDate) {
            $dt = Carbon::parse($msgDate)->setTimezone('Asia/Manila');
            $start = $dt->copy()->subMinutes(2)->format('Y-m-d H:i:s');
            $end = $dt->copy()->addMinutes(2)->format('Y-m-d H:i:s');
            $exists = DB::table('chattbl')
                ->where('sender_email', $from)
                ->whereBetween('created_at', [$start, $end])
                ->exists();
        }
    }

    if ($exists) { echo json_encode(['imported'=>0, 'reason'=>'already_exists']); exit(0); }

    $insertedId = DB::table('chattbl')->insertGetId([
        'message_id' => $messageId,
        'sender_id' => null,
        'sender_email' => $from,
        'sender_name' => $message->getFrom()[0]->personal ?? $from,
        'receiver_id' => DB::table('users')->where('email', $to)->value('id') ?? null,
        'receiver_email' => $to,
        'subject' => $message->getSubject() ?? 'No Subject',
        'message' => substr($body,0,2000),
        'sender_role' => 'email',
        'message_type' => 'incoming',
        'created_at' => Carbon::parse((string)$message->getDate())->setTimezone('Asia/Manila'),
        'updated_at' => Carbon::now('Asia/Manila'),
        'timestamp_normalized' => Carbon::parse((string)$message->getDate())->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
    ]);

    $client->disconnect();
    echo json_encode(['imported'=>1,'id'=>$insertedId]);
} catch (\Throwable $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
