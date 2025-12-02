<?php

namespace App\Http\Controllers;

use App\Services\EmailChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmailReceiverController extends Controller
{
    protected $emailService;

    public function __construct(EmailChatService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function fetchEmails()
    {
        try {
            $result = $this->emailService->fetchNewEmails();
            
            return response()->json([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'count' => $result['count'] ?? 0
            ]);

        } catch (\Exception $e) {
            \Log::error('Fetch emails error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch emails: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmailConversation($email)
    {
        try {
            $currentUser = Auth::user();
            $currentUserEmail = $currentUser->email;
            
            \Log::info("Loading conversation between: {$currentUserEmail} and {$email}");

            // Get messages where current user and selected email are the participants
            $conversation = DB::table('chattbl')
                ->where(function($query) use ($email, $currentUserEmail) {
                    // Current user sent to selected email
                    $query->where('sender_email', $currentUserEmail)
                          ->where('receiver_email', $email);
                })
                ->orWhere(function($query) use ($email, $currentUserEmail) {
                    // Selected email sent to current user
                    $query->where('sender_email', $email)
                          ->where('receiver_email', $currentUserEmail);
                })
                ->orderBy('created_at', 'asc') // FIX: Changed to ascending order
                ->get()
                ->map(function ($message) use ($currentUserEmail) {
                    // Format the timestamp for display - use Manila timezone
                    try {
                        $createdAt = Carbon::parse($message->created_at)->setTimezone('Asia/Manila');
                        $formattedTime = $createdAt->format('M j, Y g:i A'); // FIX: Regular time format (not military)
                    } catch (\Exception $e) {
                        // Fallback if timestamp parsing fails
                        $formattedTime = 'Invalid time';
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
                        'created_at_formatted' => $formattedTime,
                        'updated_at' => $message->updated_at,
                    ];
                });

            \Log::info("Found {$conversation->count()} messages in conversation");
            
            // Debug: Log first few messages with timestamps
            if ($conversation->count() > 0) {
                \Log::debug("First message: {$conversation->first()['created_at']} -> {$conversation->first()['created_at_formatted']}");
                \Log::debug("Last message: {$conversation->last()['created_at']} -> {$conversation->last()['created_at_formatted']}");
            }

            return response()->json([
                'status' => 'success',
                'conversation' => $conversation,
                'current_user_email' => $currentUserEmail,
                'debug_info' => [
                    'total_messages' => $conversation->count(),
                    'first_timestamp' => $conversation->count() > 0 ? $conversation->first()['created_at'] : null,
                    'last_timestamp' => $conversation->count() > 0 ? $conversation->last()['created_at'] : null,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get conversation error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmailChatView()
    {
        try {
            $currentUser = Auth::user();
            $currentUserEmail = $currentUser->email;
            
            // Get users who have conversed with current user
            $users = DB::table('users')
                        ->whereNotIn('role', ['admin', 'staff'])
                        ->where('email', '!=', $currentUserEmail)
                        ->select('id', 'name', 'email')
                        ->get();

            // Get unique email conversations involving current user
            $emailConversations = DB::table('chattbl')
                        ->where(function($query) use ($currentUserEmail) {
                            $query->where('receiver_email', $currentUserEmail)
                                  ->orWhere('sender_email', $currentUserEmail);
                        })
                        ->whereNotNull('sender_email')
                        ->where('sender_email', '!=', '')
                        ->where('sender_email', '!=', $currentUserEmail)
                        ->select('sender_email', 'sender_name', 'subject', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy('sender_email')
                        ->map(function ($messages, $email) {
                            return [
                                'sender_email' => $email,
                                'sender_name' => $messages->first()->sender_name,
                                'latest_subject' => $messages->first()->subject,
                                'latest_timestamp' => $messages->first()->created_at,
                                'message_count' => $messages->count()
                            ];
                        })
                        ->values();

            \Log::info("Found {$emailConversations->count()} email conversations for user: {$currentUserEmail}");
                        
            return view('email-chat', compact('users', 'emailConversations'));
        } catch (\Exception $e) {
            \Log::error('Error loading email chat view: ' . $e->getMessage());
            return view('email-chat', ['users' => [], 'emailConversations' => []]);
        }
    }

    public function getEmailView()
    {
        return $this->getEmailChatView();
    }
}