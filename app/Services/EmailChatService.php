<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Resend\Laravel\Facades\Resend;

class EmailChatService
{
    public function __construct()
    {
        // Resend is initialized via facade, no constructor needed
    }

    /**
     * IMAP fetching has been removed. Mailjet webhooks are used to receive inbound mail.
     * This method is intentionally deprecated and will return a deprecation response.
     */
    public function fetchNewEmails()
    {
        Log::info('fetchNewEmails called but IMAP is no longer supported. Use Mailjet webhooks instead.');
        return [
            'success' => false,
            'message' => 'IMAP deprecated. Use Mailjet webhooks to receive email.',
            'count' => 0
        ];
    }

    /**
     * Process individual email message with proper timestamp handling
     */
    protected function processEmailMessage($message)
{
    try {
         // DEBUG: Log headers first
        $this->debugEmailHeaders($message);
        
        // Get the raw date for debugging
        $rawDate = $message->getDate();
        if ($rawDate) {
            $this->debugTimezoneConversions($rawDate);
        }
        
        $from = $message->getFrom();
        $senderEmail = 'unknown@sender.com';
        $senderName = 'Unknown Sender';

        if ($from && $from->first()) {
            $senderEmail = $from->first()->mail ?: 'unknown@sender.com';
            $senderName = $from->first()->personal ?: $senderEmail;
        }

        // Clean the sender email
        $senderEmail = filter_var($senderEmail, FILTER_SANITIZE_EMAIL);
        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $senderEmail = 'invalid-email@placeholder.com';
        }

        $subject = $message->getSubject() ?: 'No Subject';
        $body = $this->extractMessageBody($message);
        
        // Parse the date
        $date = $this->parseEmailDate($message);
        
        // DEBUG: Log what we're saving
        Log::info("💾 Saving email: From={$senderEmail}, Subject={$subject}, Date={$date->toDateTimeString()}");

        return [
            'subject' => $subject,
            'message' => $body,
            'sender_email' => $senderEmail,
            'sender_name' => $senderName,
            'date' => $date,
            'message_id' => $message->getMessageId() ?: uniqid(),
            'raw_date' => $message->getDate() // Add raw date for debugging
        ];
    } catch (\Exception $e) {
        Log::error('Error in processEmailMessage: ' . $e->getMessage());
        return null;
    }
}
    /**
     * Parse email date with proper timezone handling - SINGLE VERSION
     */
  protected function parseEmailDate($message)
{
    try {
        // Get the Date header from the email
        $rawDate = $message->getDate();
        
        if (!$rawDate) {
            Log::warning('No date found in email, using current Manila time');
            return Carbon::now('Asia/Manila');
        }

        Log::info("📅 Raw email date header: " . $rawDate);
        
        // Clean up the date string (remove timezone abbreviations, extra spaces)
        $rawDate = trim(preg_replace('/\s*\(.*\)/', '', $rawDate)); // Remove timezone in parentheses
        
        // Try to parse the date with Manila timezone as default
        try {
            // First try parsing with Manila timezone
            $date = Carbon::parse($rawDate, 'Asia/Manila');
            
            // Check if the parsed date seems reasonable (not in the future or too far past)
            $now = Carbon::now('Asia/Manila');
            $diffInHours = $date->diffInHours($now, false);
            
            // If date appears to be more than 24 hours in the future or more than 30 days in the past,
            // it's likely a timezone issue
            if ($diffInHours > 24 || $diffInHours < -720) {
                Log::info("⚠️ Date appears incorrect, trying alternative parsing: {$date->toDateTimeString()}");
                
                // Try parsing as UTC
                $date = Carbon::parse($rawDate, 'UTC')->setTimezone('Asia/Manila');
                Log::info("🔄 Parsed as UTC → Manila: {$date->toDateTimeString()}");
            }
            
            Log::info("✅ Parsed date: Raw: {$rawDate} → Manila: {$date->toDateTimeString()}");
            return $date;
            
        } catch (\Exception $e) {
            Log::warning("Primary parsing failed: " . $e->getMessage());
            
            // Try alternative method: Use strtotime then set timezone
            $timestamp = strtotime($rawDate);
            if ($timestamp !== false) {
                $date = Carbon::createFromTimestamp($timestamp, 'UTC')
                             ->setTimezone('Asia/Manila');
                Log::info("✅ Alternative parse: Raw: {$rawDate} → Manila: {$date->toDateTimeString()}");
                return $date;
            }
            
            throw new \Exception("Could not parse date: {$rawDate}");
        }
        
    } catch (\Exception $e) {
        Log::error('❌ Error in parseEmailDate: ' . $e->getMessage() . ' - Raw date: ' . ($rawDate ?? 'N/A'));
        return Carbon::now('Asia/Manila');
    }
}
/**
 * Debug timezone conversions for a specific date string
 */
protected function debugTimezoneConversions($rawDate)
{
    $tests = [];
    
    try {
        // Test 1: Parse as Manila
        $tests['Manila'] = Carbon::parse($rawDate, 'Asia/Manila')->toDateTimeString();
        
        // Test 2: Parse as UTC, convert to Manila
        $tests['UTC → Manila'] = Carbon::parse($rawDate, 'UTC')->setTimezone('Asia/Manila')->toDateTimeString();
        
        // Test 3: Parse with timezone from string
        $tests['Default'] = Carbon::parse($rawDate)->toDateTimeString();
        
        // Test 4: strtotime approach
        $timestamp = strtotime($rawDate);
        $tests['strtotime → Manila'] = $timestamp !== false ? 
            Carbon::createFromTimestamp($timestamp, 'UTC')->setTimezone('Asia/Manila')->toDateTimeString() : 
            'Failed';
        
        Log::info("🧪 Timezone conversion tests for: {$rawDate}");
        foreach ($tests as $method => $result) {
            Log::info("   {$method}: {$result}");
        }
        
    } catch (\Exception $e) {
        Log::error("Debug error: " . $e->getMessage());
    }
}
/**
 * Debug email headers and date information
 */
protected function debugEmailHeaders($message)
{
    try {
        Log::info("🔍 DEBUG: Email Headers Analysis");
        
        // Get all headers
        $headers = $message->getHeaders();
        
        // Log important headers
        $importantHeaders = ['Date', 'Received', 'Message-ID', 'From', 'To', 'Subject'];
        
        foreach ($importantHeaders as $header) {
            if (isset($headers->{$header})) {
                Log::info("📨 {$header}: " . $headers->{$header});
            }
        }
        
        // Log the raw date string from the email
        $rawDate = $message->getDate();
        Log::info("📅 Raw Date string from email: " . $rawDate);
        
        // Try to get timestamp
        $timestamp = strtotime($rawDate);
        Log::info("⏰ strtotime result: " . ($timestamp ? date('Y-m-d H:i:s', $timestamp) : 'false'));
        
        // Try different parsing methods
        $testMethods = [
            'Carbon::parse' => Carbon::parse($rawDate)->toDateTimeString(),
            'Carbon::parse with UTC' => Carbon::parse($rawDate . ' UTC')->toDateTimeString(),
            'Carbon::parse with Manila' => Carbon::parse($rawDate, 'Asia/Manila')->toDateTimeString(),
            'strtotime to Carbon' => Carbon::createFromTimestamp(strtotime($rawDate))->toDateTimeString(),
        ];
        
        foreach ($testMethods as $method => $result) {
            Log::info("🧪 {$method}: {$result}");
        }
        
    } catch (\Exception $e) {
        Log::error("Debug error: " . $e->getMessage());
    }
}
    /**
     * Extract clean message body
     */
    protected function extractMessageBody($message)
    {
        try {
            $body = '';
            
            // Try to get text body first
            $body = $message->getTextBody();
            
            // If no text body, try HTML body
            if (!$body) {
                $body = $message->getHTMLBody();
                if ($body) {
                    // Convert HTML to plain text
                    $body = strip_tags($body);
                    // Decode HTML entities
                    $body = html_entity_decode($body);
                }
            }

            // Clean up the body
            if ($body) {
                // Normalize whitespace
                $body = trim(preg_replace('/\s+/', ' ', $body));
                // Remove quoted/reply content for cleaner display
                $body = $this->removeQuotedText($body);
                // Limit length to avoid database issues
                $body = substr($body, 0, 2000);
            }

            return $body ?: 'No content available';

        } catch (\Exception $e) {
            Log::error('Error extracting body: ' . $e->getMessage());
            return 'No content available';
        }
    }

    /**
     * Remove quoted/reply text from email body
     */
    protected function removeQuotedText($body)
    {
        // Remove common reply patterns
        $patterns = [
            '/On.*wrote:.*$/s', // "On [date] [person] wrote:"
            '/From:.*$/s', // "From: email@example.com"
            '/Sent:.*$/s', // "Sent: date"
            '/To:.*$/s', // "To: email@example.com"
            '/Subject:.*$/s', // "Subject: ..."
            '/‐+.*Original Message.*‐+/s', // "--- Original Message ---"
        ];
        
        foreach ($patterns as $pattern) {
            $body = preg_replace($pattern, '', $body);
        }
        
        return trim($body);
    }

    /**
     * Save email to database with proper timestamp
     */
    protected function saveEmailToDatabase($emailData)
    {
        try {
            // Use provided receiver_email if present, otherwise fallback to system sender address
            $normalizedReceiver = isset($emailData['receiver_email']) ? strtolower(trim($emailData['receiver_email'])) : strtolower(trim(env('MAIL_FROM_ADDRESS', 'legalconnect681@gmail.com')));

            // Resolve receiver_id if exists in users table
            $userId = DB::table('users')->where('email', $normalizedReceiver)->value('id') ?? null;

            // Parse and normalize timestamp
            $timestamp = $emailData['date'] ?? Carbon::now('Asia/Manila');
            if (!$timestamp instanceof Carbon) {
                $timestamp = Carbon::parse($timestamp, 'Asia/Manila');
            }
            $manilaTime = $timestamp->copy()->setTimezone('Asia/Manila');
            $timestampNormalized = $manilaTime->format('Y-m-d H:i:s');

            Log::info("💾 Saving Mailjet inbound with Manila time: {$manilaTime->format('M j, Y g:i A')} (DB: {$timestampNormalized})");

            // Normalize sender
            $normalizedSender = strtolower(trim($emailData['sender_email'] ?? 'unknown@sender.com'));

            // Use message_id if available as a primary dedupe key
            $messageId = $emailData['message_id'] ?? null;

            if ($messageId && Schema::hasColumn('chattbl', 'message_id')) {
                $existsById = DB::table('chattbl')
                    ->where('message_id', $messageId)
                    ->first();

                if ($existsById) {
                    Log::info("Duplicate inbound email found by message_id, skipping. ID: {$existsById->id}");
                    return false;
                }
            }

            // Fallback duplicate check (subject/message/time window)
            $existingQuery = DB::table('chattbl')
                ->where('sender_email', $normalizedSender)
                ->where('receiver_email', $normalizedReceiver)
                ->where('subject', $emailData['subject'])
                ->where('message', $emailData['message'])
                ->whereBetween('timestamp_normalized', [
                    Carbon::parse($manilaTime)->copy()->subMinutes(2),
                    Carbon::parse($manilaTime)->copy()->addMinutes(2)
                ])
                ->first();

            if ($existingQuery) {
                Log::info("Duplicate inbound email found (fallback), skipping. ID: {$existingQuery->id}");
                return false;
            }

            $insertData = [
                'sender_id' => 0,
                'sender_email' => $normalizedSender,
                'sender_name' => $emailData['sender_name'] ?? $normalizedSender,
                'receiver_id' => $userId,
                'receiver_email' => $normalizedReceiver,
                'subject' => $emailData['subject'] ?? 'No Subject',
                'message' => $emailData['message'] ?? '',
                'sender_role' => 'email',
                'message_type' => 'incoming',
                'created_at' => $manilaTime,
                'updated_at' => $manilaTime,
                'timestamp_normalized' => $timestampNormalized,
            ];

            if (Schema::hasColumn('chattbl', 'message_id')) {
                if ($messageId) {
                    $insertData['message_id'] = $messageId;
                } else {
                    $insertData['message_id'] = md5($normalizedSender . '|' . $insertData['subject'] . '|' . $timestampNormalized);
                }
            }

            $inserted = DB::table('chattbl')->insert($insertData);

            if ($inserted) {
                Log::info("✅ Mailjet inbound saved: {$normalizedSender} -> {$normalizedReceiver} at {$manilaTime}");
                return true;
            }

            Log::error('❌ Failed to insert Mailjet inbound email');
            return false;
        } catch (\Exception $e) {
            Log::error('Error saving inbound Mailjet email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using Resend API and save outgoing to DB
     */
    public function sendEmailReply($to, $subject, $body, $html = null, array $attachments = [])
    {
        try {
            Log::info("📤 Attempting to send email via Resend to: {$to}, Subject: {$subject}");

            $toList = is_array($to) ? $to : explode(',', $to);
            $toList = array_map('trim', $toList);

            // Prepare email data for Resend
            $emailData = [
                'from' => env('MAIL_FROM_ADDRESS', 'legalconnect681@gmail.com'),
                'to' => $toList,
                'subject' => $subject,
            ];

            // Use HTML if provided, otherwise use text body
            if ($html) {
                $emailData['html'] = $html;
            } else {
                $emailData['text'] = $body;
            }

            // Handle attachments for Resend (Resend expects base64 encoded attachments)
            if (!empty($attachments)) {
                $resendAttachments = [];
                foreach ($attachments as $att) {
                    if (isset($att['base64']) && isset($att['name']) && isset($att['type'])) {
                        $resendAttachments[] = [
                            'filename' => $att['name'],
                            'content' => $att['base64'],
                            'type' => $att['type'],
                        ];
                    }
                }
                if (!empty($resendAttachments)) {
                    $emailData['attachments'] = $resendAttachments;
                }
            }

            // Send email via Resend
            $result = Resend::emails()->send($emailData);

            if ($result) {
                // Save outgoing message to DB for history
                $now = Carbon::now('Asia/Manila');
                $nowString = $now->format('Y-m-d H:i:s');

                $insert = [
                    'sender_id' => auth()->id() ?? 0,
                    'sender_email' => auth()->user()->email ?? env('MAIL_FROM_ADDRESS', 'legalconnect681@gmail.com'),
                    'sender_name' => auth()->user()->name ?? env('MAIL_FROM_NAME', 'LegalConnect'),
                    'receiver_email' => implode(',', $toList),
                    'subject' => $subject,
                    'message' => $body,
                    'sender_role' => auth()->user()->role ?? 'staff',
                    'message_type' => 'outgoing',
                    'created_at' => $nowString,
                    'updated_at' => $nowString,
                    'timestamp_normalized' => $nowString,
                ];

                // Store the Resend message ID if available
                if (isset($result['id'])) {
                    $insert['message_id'] = $result['id'];
                }

                DB::table('chattbl')->insert($insert);

                return ['success' => true, 'response' => $result];
            }

            return ['success' => false, 'error' => 'Resend API error'];

        } catch (\Exception $e) {
            Log::error('❌ Resend sending failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * syncInboxFromGmail is deprecated. Use Mailjet webhooks to receive inbound email. This stub keeps compatibility
     * for any calls that still exist elsewhere and returns immediately.
     */
    public static function syncInboxFromGmail($targetEmail, $force = false)
    {
        Log::warning('syncInboxFromGmail called but IMAP sync is deprecated. Use Mailjet webhooks.');
        return;
    }



    /**
     * Handle inbound payload from Mailjet webhook
     * Accepts both parse API inbound messages and Events API format
     */
    public function handleMailjetInbound(array $payload)
    {
        // Detect inbound message pattern
        try {
            // If Events API: array of events
            if (isset($payload[0]) && is_array($payload[0]) && isset($payload[0]['Event'])) {
                foreach ($payload as $event) {
                    $this->processMailjetEvent($event);
                }
                return true;
            }

            // If Parse API style (single message payload)
            // Known fields: From, To, Subject, 'Text-part', 'HTML-part', MessageID, Date
            $from = $payload['From'] ?? ($payload['from'] ?? null);
            $to = $payload['To'] ?? ($payload['to'] ?? null);
            $subject = $payload['Subject'] ?? ($payload['subject'] ?? 'No Subject');
            $text = $payload['Text-part'] ?? ($payload['text'] ?? ($payload['text_part'] ?? ''));
            $html = $payload['HTML-part'] ?? ($payload['html'] ?? null);
            $messageId = $payload['MessageID'] ?? ($payload['MessageID'] ?? null);
            $date = $payload['Date'] ?? now();

            // Normalize From (may be 'Name <email>')
            $sender_email = $this->extractEmailFromString($from);
            $receiver_email = $this->extractEmailFromString($to);

            $emailData = [
                'sender_email' => $sender_email,
                'sender_name' => $this->extractNameFromString($from) ?? $sender_email,
                'receiver_email' => $receiver_email,
                'subject' => $subject,
                'message' => $text ?: strip_tags($html ?? ''),
                'date' => $date,
                'message_id' => $messageId,
            ];

            return $this->saveEmailToDatabase($emailData);

        } catch (\Exception $e) {
            Log::error('Error handling Mailjet inbound payload: ' . $e->getMessage());
            return false;
        }
    }

    protected function processMailjetEvent(array $event)
    {
        // Basic handling for events like sent, delivered, bounce, open
        try {
            $evt = $event['Event'] ?? $event['event'] ?? null;
            $messageId = $event['MessageID'] ?? $event['MessageID'] ?? ($event['MessageID'] ?? null);
            $time = $event['Time'] ?? now();

            // For now we store events in a simple table if exists
            if (\Schema::hasTable('mailjet_events')) {
                DB::table('mailjet_events')->insert([
                    'message_id' => $messageId,
                    'event' => $evt,
                    'payload' => json_encode($event),
                    'created_at' => Carbon::parse($time)->format('Y-m-d H:i:s')
                ]);
            }

            // If event references a message_id (which we stored as CustomID in outgoing), update chattbl status
            if ($messageId && Schema::hasColumn('chattbl', 'message_id')) {
                DB::table('chattbl')->where('message_id', $messageId)->update(['updated_at' => now()]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error processing mailjet event: ' . $e->getMessage());
            return false;
        }
    }

    protected function extractEmailFromString($addr)
    {
        if (!$addr) return null;
        // If it is an array
        if (is_array($addr)) {
            $first = reset($addr);
            if (is_array($first) && isset($first['email'])) return strtolower(trim($first['email']));
            if (isset($first['mail'])) return strtolower(trim($first['mail']));
            if (isset($first['Email'])) return strtolower(trim($first['Email']));
            if (isset($first[0])) return strtolower(trim($first[0]));
        }
        // Try to extract with regex
        if (preg_match('/<([^>]+)>/', $addr, $m)) return strtolower(trim($m[1]));
        // fallback if pure email
        if (filter_var($addr, FILTER_VALIDATE_EMAIL)) return strtolower(trim($addr));
        // sometimes comma separated addresses
        $parts = preg_split('/[,;]+/', $addr);
        foreach ($parts as $p) {
            $p = trim($p);
            if (filter_var($p, FILTER_VALIDATE_EMAIL)) return strtolower($p);
        }
        return null;
    }

    protected function extractNameFromString($addr)
    {
        if (!$addr) return null;
        if (is_array($addr)) {
            $first = reset($addr);
            if (is_array($first) && isset($first['personal'])) return $first['personal'];
            if (is_array($first) && isset($first['Name'])) return $first['Name'];
        }
        if (preg_match('/^(.*?)\s*<[^>]+>$/', $addr, $m)) return trim($m[1]);
        return null;
    }

    /**
     * Check for new messages in a conversation
     */
    public function checkNewMessages($email)
    {
        try {
            $currentUser = auth()->user();
            if (!$currentUser) {
                return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
            }
            $currentUserEmail = $currentUser->email;

            // Check for new messages in the conversation
            $newMessages = DB::table('chattbl')
                ->where(function($query) use ($email, $currentUserEmail) {
                    $query->where('sender_email', $currentUserEmail)
                        ->where('receiver_email', $email);
                })
                ->orWhere(function($query) use ($email, $currentUserEmail) {
                    $query->where('sender_email', $email)
                        ->where('receiver_email', $currentUserEmail);
                })
                ->orderBy('timestamp_normalized', 'asc')
                ->get()
                ->map(function ($message) {
                    $timestamp = $message->timestamp_normalized ?? $message->created_at;

                    if ($timestamp) {
                        $createdAt = Carbon::parse($timestamp, 'Asia/Manila');
                        $formattedTime = $createdAt->format('M j, Y g:i A');
                        $sortTimestamp = $createdAt->timestamp * 1000;
                    } else {
                        $formattedTime = 'Unknown time';
                        $sortTimestamp = 0;
                    }

                    return [
                        'id' => $message->id,
                        'sender_email' => $message->sender_email,
                        'sender_name' => $message->sender_name,
                        'receiver_email' => $message->receiver_email,
                        'subject' => $message->subject,
                        'message' => $message->message,
                        'sender_role' => $message->sender_role,
                        'message_type' => $message->message_type,
                        'created_at' => $message->created_at,
                        'timestamp_normalized' => $message->timestamp_normalized,
                        'created_at_formatted' => $formattedTime,
                        'sort_timestamp' => $sortTimestamp,
                        'updated_at' => $message->updated_at,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'has_new_messages' => $newMessages->count() > 0,
                'message_count' => $newMessages->count(),
                'messages' => $newMessages,
                'current_user_email' => $currentUserEmail
            ]);

        } catch (\Exception $e) {
            \Log::error('Check new messages error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check for new messages: ' . $e->getMessage()
            ], 500);
        }
    }

}