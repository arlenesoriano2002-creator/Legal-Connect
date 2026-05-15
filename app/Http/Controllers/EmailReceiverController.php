<?php

namespace App\Http\Controllers;

use App\Services\EmailChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\NotificationHelper;

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

    public function fetchConversation(Request $request)
    {
        $email = $request->email;

        // Mailjet webhooks deliver inbound mail (no IMAP). Fetch conversation directly from DB.
        // ✅ 2. Fetch ONLY from DB (ordered by created_at)
        // Read from `chattbl` (where EmailChatService now saves synced messages)
        $messages = DB::table('chattbl')
            ->where(function ($q) use ($email) {
                $q->where('sender_email', $email)
                  ->orWhere('receiver_email', $email);
            })
            // Exclude incoming messages whose sender is not a registered user
            ->where(function($q) {
                $q->where('message_type', '!=', 'incoming')
                  ->orWhereExists(function($q2){
                      $q2->select(DB::raw(1))->from('users')->whereRaw('users.email = chattbl.sender_email');
                  });
            })
            ->orderBy('timestamp_normalized', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function getEmailConversation($email)
    {
        try {
            $currentUser = Auth::user();
            
            if (!$currentUser) {
                \Log::error('User not authenticated when trying to load conversation for email: ' . $email);
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated. Please log in.'
                ], 401);
            }
            
            $currentUserEmail = strtolower(trim($currentUser->email));
            $email = strtolower(trim($email));
            \Log::info("Loading conversation between: {$currentUserEmail} and {$email}");

            // FIX: Get messages specifically between current user and the target email
            // This includes messages where:
            // 1. Current user sent to target email
            // 2. Target email sent to current user
            \Log::debug("Querying conversation for participants: currentUser={$currentUserEmail}, other={$email}");
            $baseQuery = DB::table('chattbl')
                ->where(function($query) use ($currentUserEmail, $email) {
                    $query->where('sender_email', $currentUserEmail)
                        ->where('receiver_email', $email);
                })
                ->orWhere(function($query) use ($currentUserEmail, $email) {
                    $query->where('sender_email', $email)
                        ->where('receiver_email', $currentUserEmail);
                })
                // Include self-sent incoming messages (sender == receiver == current user)
                ->orWhere(function($query) use ($currentUserEmail) {
                    $query->where('sender_email', $currentUserEmail)
                          ->where('receiver_email', $currentUserEmail)
                          ->where('message_type', 'incoming');
                });

            // Exclude incoming messages whose sender is not a registered user
            $conversation = $baseQuery
                ->where(function($q) {
                    $q->where('message_type', '!=', 'incoming')
                      ->orWhereExists(function($q2){
                          $q2->select(DB::raw(1))->from('users')->whereRaw('users.email = chattbl.sender_email');
                      });
                })
                ->orderBy('timestamp_normalized', 'asc')
                ->get()
                ->map(function ($message) {
                    // ✅ FIX: Use Manila timezone consistently
                    $timestamp = $message->timestamp_normalized ?? $message->created_at;
                    
                    if ($timestamp) {
                        // Parse with Manila timezone
                        $createdAt = Carbon::parse($timestamp, 'Asia/Manila');
                        
                        // Format for display (e.g., "Jan 2, 2026 6:21 PM")
                        $formattedTime = $createdAt->format('M j, Y g:i A');
                        
                        // Create sort timestamp
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
            
            \Log::info("Found {$conversation->count()} messages in conversation");
            
            // Debug log to see what's being fetched
            \Log::debug("Conversation data:", [
                'current_user' => $currentUserEmail,
                'target_email' => $email,
                'messages_count' => $conversation->count(),
                'sample_messages' => $conversation->take(3)->map(function($msg) {
                    return [
                        'id' => $msg['id'],
                        'sender_email' => $msg['sender_email'],
                        'receiver_email' => $msg['receiver_email'],
                        'message_type' => $msg['message_type'],
                        'sender_id_debug' => 'Not in array'
                    ];
                })
            ]);
            
            return response()->json([
                'status' => 'success',
                'conversation' => $conversation,
                'current_user_email' => $currentUserEmail,
                'debug_info' => [
                    'total_messages' => $conversation->count(),
                    'first_timestamp' => $conversation->count() > 0 ? $conversation->first()['timestamp_normalized'] : null,
                    'first_formatted' => $conversation->count() > 0 ? $conversation->first()['created_at_formatted'] : null,
                    'timezone_used' => 'Asia/Manila'
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

    /**
     * Check for new messages in a conversation
     */
    public function checkNewMessages($email)
    {
        try {
            $currentUser = Auth::user();
            
            if (!$currentUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $currentUserEmail = $currentUser->email;
            
            // Get count of messages since last check (exclude incoming from unregistered senders)
            $baseCountQuery = DB::table('chattbl')
                ->where(function($query) use ($email, $currentUserEmail) {
                    $query->where('sender_email', $currentUserEmail)
                        ->where('receiver_email', $email);
                })
                ->orWhere(function($query) use ($email, $currentUserEmail) {
                    $query->where('sender_email', $email)
                        ->where('receiver_email', $currentUserEmail);
                });

            $messageCount = $baseCountQuery
                ->where(function($q) {
                    $q->where('message_type', '!=', 'incoming')
                      ->orWhereExists(function($q2){
                          $q2->select(DB::raw(1))->from('users')->whereRaw('users.email = chattbl.sender_email');
                      });
                })
                ->count();
            
            return response()->json([
                'status' => 'success',
                'has_new_messages' => $messageCount > 0,
                'message_count' => $messageCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Check new messages error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check for new messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test endpoint to debug timestamp parsing
     */
    public function debugTimestamp(Request $request)
    {
        try {
            $testDate = "Jan. 2 2026 7:22 pm";
            
            // Test different parsing methods
            $tests = [
                'Method 1: Parse as-is' => Carbon::parse($testDate),
                'Method 2: Parse as Manila' => Carbon::parse($testDate, 'Asia/Manila'),
                'Method 3: Parse as UTC' => Carbon::parse($testDate . ' UTC'),
                'Method 4: strtotime to Carbon' => Carbon::createFromTimestamp(strtotime($testDate)),
                'Method 5: strtotime with Manila' => Carbon::createFromTimestamp(strtotime($testDate), 'Asia/Manila'),
                'Method 6: strtotime UTC to Manila' => Carbon::createFromTimestamp(strtotime($testDate . ' UTC'), 'Asia/Manila'),
            ];
            
            $results = [];
            foreach ($tests as $method => $date) {
                $results[$method] = [
                    'raw' => $date->toDateTimeString(),
                    'manila' => $date->setTimezone('Asia/Manila')->toDateTimeString(),
                    'timezone' => $date->getTimezone()->getName()
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'test_date' => $testDate,
                'current_time_manila' => Carbon::now('Asia/Manila')->toDateTimeString(),
                'tests' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return the latest message in the conversation between the current user and $email
     * Optional query param: sync=1 to trigger an IMAP sync before reading DB
     */
    public function getLatestMessage(Request $request, $email)
    {
        try {
            $currentUser = Auth::user();
            if (!$currentUser) {
                return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
            }

            $currentUserEmail = strtolower(trim($currentUser->email));
            $email = strtolower(trim($email));

            // Mailjet webhooks are the authorized inbound mechanism; IMAP sync is no longer used.

            $baseQuery = DB::table('chattbl')
                ->where(function($query) use ($currentUserEmail, $email) {
                    $query->where('sender_email', $currentUserEmail)
                          ->where('receiver_email', $email);
                })
                ->orWhere(function($query) use ($currentUserEmail, $email) {
                    $query->where('sender_email', $email)
                          ->where('receiver_email', $currentUserEmail);
                })
                ->orWhere(function($query) use ($currentUserEmail) {
                    $query->where('sender_email', $currentUserEmail)
                          ->where('receiver_email', $currentUserEmail)
                          ->where('message_type', 'incoming');
                });

            $message = $baseQuery
                ->where(function($q) {
                    $q->where('message_type', '!=', 'incoming')
                      ->orWhereExists(function($q2){
                          $q2->select(DB::raw(1))->from('users')->whereRaw('users.email = chattbl.sender_email');
                      });
                })
                ->orderBy('timestamp_normalized', 'desc')
                ->first();

            if (!$message) {
                return response()->json(['status' => 'success', 'message' => null]);
            }

            $timestamp = $message->timestamp_normalized ?? $message->created_at;
            if ($timestamp) {
                $createdAt = Carbon::parse($timestamp, 'Asia/Manila');
                $formattedTime = $createdAt->format('M j, Y g:i A');
                $sortTimestamp = $createdAt->timestamp * 1000;
            } else {
                $formattedTime = 'Unknown time';
                $sortTimestamp = 0;
            }

            $out = [
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

            return response()->json(['status' => 'success', 'latest' => $out]);

        } catch (\Exception $e) {
            \Log::error('getLatestMessage error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

   public function getEmailChatView()
{
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Please log in to access email chat');
    }

        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'superadmin', 'lawyer'])) {
            return redirect()->route('welcome')->with('error', 'Access denied. Admin privileges required.');
        }

        try {
            $currentUser = Auth::user();
            $currentUserEmail = $currentUser->email;
            
            // Get registered users with client role as email receivers
            $users = DB::table('users')
                        ->where('role', 'client')
                        ->where('email', '!=', $currentUserEmail)
                        ->select('id', 'name', 'email', 'image')
                        ->orderBy('name', 'asc')
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
                // Only include senders that exist as registered users
                ->whereExists(function($q){
                    $q->select(DB::raw(1))->from('users')->whereRaw('users.email = chattbl.sender_email');
                })
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

    /**
     * Return all incoming messages for the IMAP account grouped by sender_email
     */
    public function getEmailInbox()
    {
        try {
            $imapEmail = strtolower(trim(env('IMAP_USERNAME', 'cafirma.jerome2002@gmail.com')));

            $rows = DB::table('chattbl')
                ->where('message_type', 'incoming')
                ->where('receiver_email', $imapEmail)
                // Exclude incoming messages where sender isn't a registered user
                ->whereExists(function($q){
                    $q->select(DB::raw(1))->from('users')->whereRaw('users.email = chattbl.sender_email');
                })
                ->orderBy('timestamp_normalized', 'desc')
                ->get()
                ->groupBy('sender_email')
                ->map(function ($messages, $sender) {
                    $first = $messages->first();
                    return [
                        'sender_email' => $sender,
                        'sender_name' => $first->sender_name,
                        'latest_subject' => $first->subject,
                        'latest_message' => $first->message,
                        'latest_timestamp' => $first->timestamp_normalized,
                        'message_count' => $messages->count()
                    ];
                })->values();

            return response()->json([
                'status' => 'success',
                'imap_email' => $imapEmail,
                'conversations' => $rows
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading email inbox: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getEmailView()
    {
        return $this->getEmailChatView();
    }
    
}