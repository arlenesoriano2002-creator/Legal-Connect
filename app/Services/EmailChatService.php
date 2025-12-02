<?php

namespace App\Services;

use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailChatService
{
    protected $client;

    public function __construct()
    {
        try {
            if (empty(env('IMAP_USERNAME')) || empty(env('IMAP_PASSWORD'))) {
                throw new \Exception('IMAP credentials not configured in environment');
            }
            
            $this->client = Client::account('gmail');
            Log::info('✅ IMAP client initialized successfully');
        } catch (\Exception $e) {
            Log::error('❌ IMAP client initialization failed: ' . $e->getMessage());
            $this->client = null;
        }
    }

    /**
     * Fetch new emails with timeout handling
     */
    public function fetchNewEmails()
    {
        if (!$this->client) {
            Log::error('❌ IMAP client not available');
            return [
                'success' => false,
                'message' => 'IMAP client not configured properly',
                'count' => 0
            ];
        }

        try {
            Log::info('🔔 Starting IMAP email fetch process...');
            
            // Set a timeout for the IMAP connection
            set_time_limit(60); // 60 seconds timeout
            
            if (!$this->client->isConnected()) {
                $this->client->connect();
            }
            
            Log::info('✅ IMAP connected successfully');
            
            $folder = $this->client->getFolder('INBOX');
            Log::info('✅ INBOX folder accessed');
            
            // Get messages with shorter time range for better performance
            $messages = $folder->query()
                ->since(now()->subDays(7)) // Reduced from 30 to 7 days
                ->limit(10) // Reduced limit
                ->leaveUnread()
                ->get();

            Log::info("📧 Found {$messages->count()} total messages in INBOX");

            $processedCount = 0;
            $errorCount = 0;

            foreach ($messages as $message) {
                try {
                    $emailData = $this->processEmailMessage($message);
                    
                    if ($emailData && $this->saveEmailToDatabase($emailData)) {
                        $processedCount++;
                        Log::info("✅ Processed email from: {$emailData['sender_email']} at {$emailData['date']}");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Error processing individual email: ' . $e->getMessage());
                    continue;
                }
            }

            $this->client->disconnect();

            Log::info("🎉 Successfully processed {$processedCount} emails, {$errorCount} errors");
            
            return [
                'success' => true,
                'message' => $processedCount > 0 ? 
                    "Fetched {$processedCount} emails" : 
                    "No new emails found",
                'count' => $processedCount
            ];

        } catch (\Exception $e) {
            Log::error('❌ IMAP Connection Error: ' . $e->getMessage());
            
            // Ensure client is disconnected on error
            try {
                if ($this->client && $this->client->isConnected()) {
                    $this->client->disconnect();
                }
            } catch (\Exception $disconnectError) {
                Log::error('Error disconnecting IMAP: ' . $disconnectError->getMessage());
            }
            
            return [
                'success' => false,
                'message' => 'IMAP Connection Failed: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Process individual email message with proper timestamp handling
     */
    protected function processEmailMessage($message)
    {
        try {
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
            
            // FIX: Use Manila time consistently
            $date = $this->parseEmailDate($message);

            return [
                'subject' => $subject,
                'message' => $body,
                'sender_email' => $senderEmail,
                'sender_name' => $senderName,
                'date' => $date,
                'message_id' => $message->getMessageId() ?: uniqid()
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
            $rawDate = $message->getDate();
            
            if (!$rawDate) {
                Log::warning('No date found in email, using current Manila time');
                return Carbon::now('Asia/Manila');
            }

            // Parse the date string and convert to Manila timezone
            $parsedDate = Carbon::parse($rawDate)->setTimezone('Asia/Manila');
            
            Log::info("Date conversion - Raw: {$rawDate}, Manila: {$parsedDate}");
            
            return $parsedDate;
            
        } catch (\Exception $e) {
            Log::error('Error parsing email date: ' . $e->getMessage() . ' - Raw date: ' . $rawDate);
            return Carbon::now('Asia/Manila');
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
            $currentUser = \Illuminate\Support\Facades\Auth::user();
            $userEmail = $currentUser ? $currentUser->email : env('IMAP_USERNAME', 'cafirma.jerome2002@gmail.com');

            // Enhanced duplicate check
            $existingQuery = DB::table('chattbl')
                ->where('sender_email', $emailData['sender_email'])
                ->where('receiver_email', $userEmail)
                ->where('subject', $emailData['subject'])
                ->where('message', $emailData['message'])
                ->whereBetween('created_at', [
                    $emailData['date']->copy()->subMinutes(2),
                    $emailData['date']->copy()->addMinutes(2)
                ])
                ->first();

            if ($existingQuery) {
                Log::info("Duplicate email found, skipping. ID: {$existingQuery->id}");
                return false;
            }

            // Insert the email
            $insertData = [
                'sender_id' => 0,
                'sender_email' => $emailData['sender_email'],
                'sender_name' => $emailData['sender_name'],
                'receiver_id' => $currentUser ? $currentUser->id : null,
                'receiver_email' => $userEmail,
                'subject' => $emailData['subject'],
                'message' => $emailData['message'],
                'sender_role' => 'email',
                'message_type' => 'incoming',
                'created_at' => $emailData['date'],
                'updated_at' => $emailData['date'],
            ];

            $inserted = DB::table('chattbl')->insert($insertData);

            if ($inserted) {
                Log::info("✅ Email saved: {$emailData['sender_email']} -> {$userEmail} at {$emailData['date']}");
                return true;
            } else {
                Log::error("❌ Failed to insert email");
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Error saving email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with improved error handling
     */
    public function sendEmailReply($to, $subject, $body)
    {
        try {
            Log::info("📤 Attempting to send email to: {$to}, Subject: {$subject}");

            // Use Laravel's raw method - MOST RELIABLE
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                       ->subject($subject)
                       ->from(
                           env('MAIL_FROM_ADDRESS', 'noreply@legalconnect.com'),
                           env('MAIL_FROM_NAME', 'LegalConnect')
                       );
            });

            Log::info("✅ Email sent successfully to: {$to}");
            return ['success' => true, 'message' => 'Email sent successfully'];
            
        } catch (\Exception $e) {
            Log::error('❌ Email sending failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}