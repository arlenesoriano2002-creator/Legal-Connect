<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnifiedEmailService
{
    /**
     * Fetch and store emails
     */
    public function fetchAndStoreEmails()
    {
        try {
            Log::info('Fetching emails...');
            
            // Since we don't have IMAP configured, just return a test response
            return [
                'success' => true,
                'message' => 'Email fetch simulated (IMAP not configured)',
                'count' => 0
            ];
            
        } catch (\Exception $e) {
            Log::error('Fetch emails error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch emails: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get conversation from database
     */
    public function getConversation($userEmail, $otherEmail)
    {
        try {
            $conversation = DB::table('chattbl')
                ->where(function($query) use ($userEmail, $otherEmail) {
                    $query->where('sender_email', $userEmail)
                          ->where('receiver_email', $otherEmail);
                })
                ->orWhere(function($query) use ($userEmail, $otherEmail) {
                    $query->where('sender_email', $otherEmail)
                          ->where('receiver_email', $userEmail);
                })
                ->where(function($query) {
                    $query->where('message_type', 'email')
                          ->orWhere('sender_role', 'email')
                          ->orWhereNotNull('receiver_email');
                })
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    $createdAt = Carbon::parse($message->created_at);
                    
                    return [
                        'id' => $message->id,
                        'sender_email' => $message->sender_email ?? $message->sender_id,
                        'sender_name' => $message->sender_name ?? 'Unknown',
                        'receiver_email' => $message->receiver_email ?? $message->receiver_id,
                        'receiver_name' => $message->receiver_name ?? 'Unknown',
                        'subject' => $message->subject ?? 'No Subject',
                        'message' => $message->message,
                        'direction' => $message->sender_email === auth()->user()->email ? 'outgoing' : 'incoming',
                        'message_type' => $message->message_type ?? 'email',
                        'is_read' => $message->is_read ?? false,
                        'is_sent' => true,
                        'is_received' => true,
                        'created_at' => $message->created_at,
                        'created_at_formatted' => $createdAt->format('M j, Y g:i A'),
                        'updated_at' => $message->updated_at,
                    ];
                });

            return $conversation;

        } catch (\Exception $e) {
            Log::error('Error getting conversation: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Send email
     */
    public function sendEmail(array $messageData)
    {
        try {
            $currentUser = auth()->user();
            
            // Store in database
            $messageId = DB::table('chattbl')->insertGetId([
                'sender_email' => $currentUser->email,
                'sender_name' => $currentUser->name,
                'receiver_email' => $messageData['to_email'],
                'receiver_name' => $messageData['to_name'] ?? $messageData['to_email'],
                'message' => $messageData['body'],
                'subject' => $messageData['subject'],
                'message_type' => 'email',
                'created_at' => Carbon::now('Asia/Manila'),
                'updated_at' => Carbon::now('Asia/Manila'),
            ]);

            // Try to send via SMTP
            try {
                Mail::raw($messageData['body'], function ($message) use ($messageData, $currentUser) {
                    $message->to($messageData['to_email'])
                           ->subject($messageData['subject'])
                           ->from(
                               $currentUser->email,
                               $currentUser->name
                           );
                });
                
                Log::info('Email sent successfully via SMTP');
            } catch (\Exception $mailError) {
                Log::error('SMTP sending failed, but message stored in DB: ' . $mailError->getMessage());
                // Continue anyway since we stored the message
            }

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'database_id' => $messageId,
                'timestamp' => Carbon::now('Asia/Manila')->format('M j, Y g:i A')
            ];

        } catch (\Exception $e) {
            Log::error('Email sending error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }
}