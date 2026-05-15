<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;

class SmsChatController extends Controller
{
    // iProgSMS only
    private $iprogApiKey;
    private $iprogApiUrl;

    public function __construct()
    {
        $this->iprogApiKey = env('IPROG_API_KEY', '2b1baa0c58ab4c509dc93e086c2cb2eccd0e6e44');
        $this->iprogApiUrl = rtrim(env('IPROG_API_URL', 'https://www.iprogsms.com/api'), '/');
    }

    public function sendViaIprog($phoneNumber, $message)
    {
        Log::info('Calling iProgSMS API (v1/sms_messages) for phone: ' . $phoneNumber);

        if (empty($this->iprogApiKey) || empty($this->iprogApiUrl)) {
            Log::warning('iProgSMS API key or URL not configured');
            return [
                'success' => false,
                'error' => 'iProgSMS credentials not configured'
            ];
        }

        // Prepare phone in local format (e.g., 09916156687). If number is in 63... format, convert to 0...
        $digits = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (strlen($digits) === 12 && substr($digits, 0, 2) === '63') {
            $phoneParam = '0' . substr($digits, 2);
        } elseif (strlen($digits) === 10) {
            // 10-digit like 9171234567 -> prefix 0
            $phoneParam = '0' . $digits;
        } else {
            // keep as-is for 11-digit local numbers or others
            $phoneParam = $digits;
        }

        $url = rtrim($this->iprogApiUrl, '/') . '/v1/sms_messages';

        $payload = [
            'api_token' => $this->iprogApiKey,
            'message' => $message,
            'phone_number' => $phoneParam
        ];

        Log::info('iProgSMS Request URL: ' . $url);
        Log::info('iProgSMS Form Payload: ' . http_build_query($payload));

        try {
            // iProg example uses x-www-form-urlencoded
            $response = Http::asForm()->timeout(30)->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ])->post($url, $payload);

            Log::info('iProgSMS Response Status: ' . $response->status());
            Log::info('iProgSMS Response Body: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();

                $messageId = $data['id'] ?? $data['message_id'] ?? data_get($data, 'data.id');
                $status = $data['status'] ?? data_get($data, 'data.status') ?? 'sent';

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'status' => $this->normalizeStatus($status),
                    'response' => $data,
                    'error' => null
                ];
            }

            $errBody = $response->body();
            $errData = json_decode($errBody, true);
            $errMsg = $errData['error'] ?? $errData['message'] ?? $errBody;

            return [
                'success' => false,
                'error' => 'Provider error: ' . $errMsg,
                'response' => $errData ?? $errBody
            ];

        } catch (\Exception $e) {
            Log::error('iProgSMS Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }
    public function index()
    {
        $users = User::whereNotNull('cp_number')
                    ->where('cp_number', '!=', '')
                    ->orderBy('name')
                    ->get();

        // Get unique SMS conversations
        $smsConversations = SmsMessage::select('receiver_id', 'phone_number')
            ->whereNotNull('receiver_id')
            ->groupBy('receiver_id', 'phone_number')
            ->with('receiver')
            ->get()
            ->groupBy('receiver_id');

        return view('admin.sms-chat', compact('users', 'smsConversations'));
    }

    public function getConversation($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $currentUserId = auth()->id();
            
            // Get all messages between current user and the selected user
            $messages = SmsMessage::where(function($query) use ($userId, $currentUserId) {
                $query->where('sender_id', $currentUserId)
                      ->where('receiver_id', $userId);
            })->orWhere(function($query) use ($userId, $currentUserId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $currentUserId);
            })
            ->orderBy('created_at', 'asc')
            ->with(['sender', 'receiver'])
            ->get();

            // Format messages for display
            $formattedMessages = $messages->map(function($msg) use ($currentUserId) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'phone_number' => $msg->phone_number,
                    'formatted_phone' => $this->formatPhoneNumber($msg->phone_number),
                    'message_type' => $msg->message_type,
                    'is_incoming' => $msg->sender_id !== $currentUserId,
                    'sender_name' => $msg->sender ? $msg->sender->name : 'System',
                    'created_at' => $msg->created_at,
                    'created_at_formatted' => $msg->formatted_time,
                    'status' => $msg->status
                ];
            });

            return response()->json([
                'status' => 'success',
                'conversation' => $formattedMessages,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->cp_number,
                    'formatted_phone' => $this->formatPhoneNumber($user->cp_number)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching SMS conversation: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load conversation'
            ], 500);
        }
        NotificationHelper::createMessageNotification(
            'sms',
            'New SMS from ' . $senderPhone,
            substr($message, 0, 100),
            [
                'phone' => $senderPhone,
                'name' => $senderName
            ],
            $adminId // The admin who should receive this
        );

    }

    public function sendSms(Request $request)
    {
        // Accept either to_user_id (existing user) or phone_number (raw send from modal).
        $request->validate([
            'to_user_id' => 'nullable|exists:users,id',
            'phone_number' => 'nullable|string',
            'message' => 'required|string|max:160'
        ]);

        if (empty($request->to_user_id) && empty($request->phone_number)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Either to_user_id or phone_number is required.'
            ], 422);
        }

        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if (!empty($request->to_user_id)) {
                $receiver = User::findOrFail($request->to_user_id);
                $targetPhone = $this->formatPhoneForApi($receiver->cp_number);
                $dbPhone = $receiver->cp_number;
            } else {
                // Raw phone number send from modal
                $receiver = null;
                $targetPhone = $this->formatPhoneForApi($request->phone_number);
                $dbPhone = $request->phone_number;
            }

            if ($receiver) {
                Log::info('Attempting to send SMS to user ID: ' . $receiver->id);
                Log::info('Receiver phone: ' . $receiver->cp_number);
            } else {
                Log::info('Attempting to send SMS to raw phone: ' . $dbPhone);
            }
            Log::info('Message: ' . substr($request->message, 0, 50) . '...');

            if (empty($dbPhone)) {
                Log::error('No phone number provided');
                return response()->json([
                    'status' => 'error',
                    'message' => 'User does not have a valid phone number'
                ], 400);
            }

            // Use targetPhone determined above
            $formattedPhone = $targetPhone;
            Log::info('Formatted phone for API: ' . $formattedPhone);

            // Prevent accidental duplicate sends: check for identical recent successful outgoing message
            // Use receiver_id when available, otherwise use phone_number
            if ($receiver) {
                $recentSuccessful = SmsMessage::where('receiver_id', $receiver->id)
                    ->where('message', $request->message)
                    ->where('message_type', 'outgoing')
                    ->where('status', '!=', 'failed')
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->exists();

                if ($recentSuccessful) {
                    Log::warning('Duplicate SMS prevented for user ' . $receiver->id . ' (recent successful exists)');
                    $existing = SmsMessage::where('receiver_id', $receiver->id)
                        ->where('message', $request->message)
                        ->where('message_type', 'outgoing')
                        ->where('status', '!=', 'failed')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Duplicate message prevented; recent send already succeeded.',
                        'sms' => $existing
                    ], 200);
                }
            } else {
                $recentSuccessful = SmsMessage::where('phone_number', $dbPhone)
                    ->where('message', $request->message)
                    ->where('message_type', 'outgoing')
                    ->where('status', '!=', 'failed')
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->exists();

                if ($recentSuccessful) {
                    Log::warning('Duplicate SMS prevented for phone ' . $dbPhone . ' (recent successful exists)');
                    $existing = SmsMessage::where('phone_number', $dbPhone)
                        ->where('message', $request->message)
                        ->where('message_type', 'outgoing')
                        ->where('status', '!=', 'failed')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Duplicate message prevented; recent send already succeeded.',
                        'sms' => $existing
                    ], 200);
                }
            }

            // Use iProgSMS as primary SMS provider (TextBee proved unreliable)
            $response = $this->sendViaIprog($formattedPhone, $request->message);
            $provider = 'iprog';

            if ($response['success']) {
                // Save outgoing message
                $savedStatus = $this->normalizeStatus($response['status'] ?? 'sent');

                $sms = SmsMessage::create([
                    'sender_id' => $currentUser->id,
                    // If no user receiver (raw phone), set receiver_id to sender to satisfy NOT NULL column.
                    'receiver_id' => $receiver ? $receiver->id : $currentUser->id,
                    'phone_number' => $dbPhone,
                    'message' => $request->message,
                    'message_type' => 'outgoing',
                    'status' => $savedStatus,
                    'message_id' => $response['message_id'] ?? null
                ]);

                Log::info('SMS API returned success; saved with status: ' . ($response['status'] ?? 'unknown'));

                // Remove any recent failed duplicates of the same message to keep the UI clean
                try {
                    $q = SmsMessage::where('message', $request->message)
                        ->where('message_type', 'outgoing')
                        ->where('status', 'failed')
                        ->where('created_at', '>=', now()->subMinutes(5));
                    if ($receiver) {
                        $q->where('receiver_id', $receiver->id);
                    } else {
                        $q->where('phone_number', $dbPhone);
                    }
                    $q->delete();
                } catch (\Exception $e) {
                    Log::warning('Failed to cleanup duplicate failed SMS records: ' . $e->getMessage());
                }

                // If provider returned 'queued', provide actionable feedback
                if (($response['status'] ?? '') === 'queued') {
                    if ($provider === 'textbee') {
                        try {
                            $deviceResp = Http::withHeaders([
                                'x-api-key' => $this->textBeeApiKey,
                                'Accept' => 'application/json'
                            ])->get($this->textBeeApiUrl . '/gateway/devices/' . $this->deviceId);

                            $deviceData = $deviceResp->successful() ? $deviceResp->json() : null;
                            Log::info('TextBee device status: ' . json_encode($deviceData));

                            // If device is not active/connected, indicate this to the client
                            $deviceActive = $deviceData['active'] ?? ($deviceData['device_active'] ?? null);
                            $deviceStatus = $deviceData['status'] ?? ($deviceData['device_status'] ?? null);

                            if ($deviceActive === false || $deviceStatus === 'offline' || $deviceStatus === 'disconnected') {
                                // Update DB status to pending to reflect queued but not sent
                                $sms->update(['status' => $this->normalizeStatus('queued')]);

                                return response()->json([
                                    'status' => 'queued',
                                    'message' => 'SMS queued in provider dashboard but device appears offline. It will be sent when the device reconnects.',
                                    'sms' => $sms,
                                    'device' => $deviceData
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to fetch device status: ' . $e->getMessage());
                        }
                    } else {
                        // Twilio queued: inform UI but no device to check
                        $sms->update(['status' => $this->normalizeStatus('queued')]);
                        return response()->json([
                            'status' => 'queued',
                            'message' => 'SMS queued with Twilio and will be delivered when processed by Twilio.',
                            'sms' => $sms,
                            'provider_response' => $response
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'SMS sent successfully (provider queued).',
                    'sms' => $sms,
                    'provider_response' => $response
                ]);
            } else {
                // Save failed message
                SmsMessage::create([
                    'sender_id' => $currentUser->id,
                    'receiver_id' => $receiver->id,
                    'phone_number' => $receiver->cp_number,
                    'message' => $request->message,
                    'message_type' => 'outgoing',
                    'status' => 'failed'
                ]);

                Log::error('SMS sending failed: ' . $response['error']);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send SMS: ' . $response['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS to an arbitrary phone number (from New SMS modal).
     */
    public function sendSmsRaw(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        try {

            $currentUser = auth()->user();
            if (!$currentUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $phone = $request->phone_number;
            Log::info('Admin sending raw SMS to: ' . $phone . ' by user ' . $currentUser->id);

            // Format phone for API
            $formattedPhone = $this->formatPhoneForApi($phone);

            $response = $this->sendViaIprog($formattedPhone, $request->message);

            if ($response['success']) {
                $sms = SmsMessage::create([
                    'sender_id' => $currentUser->id,
                    // receiver_id cannot be null in DB schema; use sender id as placeholder for raw sends
                    'receiver_id' => $currentUser->id,
                    'phone_number' => $phone,
                    'message' => $request->message,
                    'message_type' => 'outgoing',
                    'status' => $this->normalizeStatus($response['status'] ?? 'sent'),
                    'message_id' => $response['message_id'] ?? null
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'SMS sent successfully (provider queued).',
                    'sms' => $sms,
                    'provider_response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS: ' . ($response['error'] ?? 'Unknown')
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error sending raw SMS: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendViaTextBee($phoneNumber, $message)
{
    try {
        Log::info('Calling TextBee API for phone: ' . $phoneNumber);
        Log::info('Device ID: ' . $this->deviceId);
        
        // CORRECT endpoint from documentation
        $url = $this->textBeeApiUrl . '/gateway/devices/' . $this->deviceId . '/send-sms';
        
        $requestBody = [
            'recipients' => [$phoneNumber],
            'message' => $message
        ];
        
        Log::info('TextBee Request URL: ' . $url);
        Log::info('TextBee Request Body: ' . json_encode($requestBody));
        
        $response = Http::timeout(30)->withHeaders([
            'x-api-key' => $this->textBeeApiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post($url, $requestBody);
        
        Log::info('TextBee Response Status: ' . $response->status());
        Log::info('TextBee Response Body: ' . $response->body());
        
        // Accept 201 status (Created) as success
        if ($response->status() === 201 || $response->successful()) {
            $data = $response->json();
            
            // Check the actual response structure from test
            if (isset($data['data']['success']) && $data['data']['success'] === true) {
                return [
                    'success' => true,
                    'message_id' => $data['data']['smsBatchId'] ?? null,
                    'status' => 'queued', // TextBee says "added to queue"
                    'response' => $data,
                    'note' => 'SMS queued for processing'
                ];
            }
            
            // Fallback for other success structures
            return [
                'success' => true,
                'message_id' => $data['id'] ?? $data['data']['_id'] ?? null,
                'status' => 'queued',
                'response' => $data
            ];
        } else {
            $errorMessage = $response->body();
            
            // Try to parse error message
            $errorData = json_decode($errorMessage, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $errorMessage = $errorData['error'] ?? $errorData['message'] ?? $errorMessage;
            }
            
            return [
                'success' => false,
                'error' => 'HTTP ' . $response->status() . ': ' . $errorMessage,
                'http_code' => $response->status(),
                'raw_response' => $response->body()
            ];
        }
        
    } catch (\Exception $e) {
        Log::error('TextBee Connection Error: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Connection failed: ' . $e->getMessage()
        ];
    }
    
    }
    
    /**
     * Send SMS via Twilio REST API.
     * Returns array: ['success' => bool, 'message_id' => string|null, 'status' => string|null, 'response' => mixed, 'error' => string|null]
     */
    private function sendViaTwilio($phoneNumber, $message)
    {
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $from = env('TWILIO_FROM', null);

            if (empty($sid) || empty($token) || empty($from)) {
                return [
                    'success' => false,
                    'error' => 'Twilio not configured (TWILIO_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM required)'
                ];
            }

            // Ensure phone has leading +
            $to = (strpos($phoneNumber, '+') === 0) ? $phoneNumber : '+' . $phoneNumber;

            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post($url, [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message
                ]);

            Log::info('Twilio response status: ' . $response->status());
            Log::info('Twilio response body: ' . $response->body());

            if ($response->status() === 201 || $response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['sid'] ?? null,
                    'status' => $data['status'] ?? 'queued',
                    'response' => $data
                ];
            }

            $err = $response->body();
            return [
                'success' => false,
                'error' => 'HTTP ' . $response->status() . ': ' . $err,
                'http_code' => $response->status(),
                'raw_response' => $err
            ];

        } catch (\Exception $e) {
            Log::error('Twilio Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    public function testTextBeeComprehensive()
{
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    // Test with curl to get detailed info
    $url = "https://api.textbee.dev/api/v1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'base_api_response' => $response,
        'http_code' => $httpCode,
        'error' => $error,
        'suggestion' => 'Check what the base API endpoint returns. It might show available endpoints.'
    ]);
}

    public function checkDeliveryStatus($messageId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->textBeeApiKey,
                'Accept' => 'application/json'
            ])->get($this->textBeeApiUrl . '/messages/' . $messageId);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update message status in database
                // Normalize incoming status before updating DB
                $newStatus = $this->normalizeStatus($data['status'] ?? 'sent');
                SmsMessage::where('message_id', $messageId)
                    ->update(['status' => $newStatus]);

                return response()->json([
                    'status' => 'success',
                    'delivery_status' => $data['status'] ?? 'unknown'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error checking delivery status: ' . $e->getMessage());
        }

        return response()->json(['status' => 'error'], 500);
    }

    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10) {
            return '+63 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 4);
        } elseif (strlen($phone) === 11 && substr($phone, 0, 2) === '63') {
            return '+63 ' . substr($phone, 2, 3) . ' ' . substr($phone, 5, 3) . ' ' . substr($phone, 8, 4);
        }
        
        return $phone;
    }
    
    public function formatPhoneForApi($phone)
{
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If empty after cleaning, return empty
    if (empty($phone)) {
        return '';
    }
    
    // Handle different Philippine formats
    if (strlen($phone) === 10) {
        // 10-digit (e.g., 9171234567) -> add 63
        return '63' . $phone;
    } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
        // 11-digit starting with 0 (e.g., 09171234567) -> remove 0, add 63
        return '63' . substr($phone, 1);
    } elseif (strlen($phone) === 12 && substr($phone, 0, 2) === '63') {
        // Already in 639 format
        return $phone;
    }
    
    // Return as-is for other formats
    return $phone;
}

    // Test method with correct API format
    public function testTextBee()
    {
        $phoneNumber = '09171234567'; // Test with a real Philippine number
        $message = 'Test SMS from LegalConnect';
        
        $formattedPhone = $this->formatPhoneForApi($phoneNumber);
        
        Log::info('Testing TextBee API with phone: ' . $formattedPhone);
        Log::info('Device ID: ' . $this->deviceId);
        
        try {
            $url = $this->textBeeApiUrl . '/gateway/devices/' . $this->deviceId . '/sendSMS';
            
            $requestBody = [
                'message' => $message,
                'phoneNumbers' => [$formattedPhone]
            ];
            
            Log::info('Request URL: ' . $url);
            Log::info('Request Body: ' . json_encode($requestBody));
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->textBeeApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($url, $requestBody);
            
            Log::info('TextBee Response Status: ' . $response->status());
            Log::info('TextBee Response Body: ' . $response->body());
            
            return response()->json([
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => $response->body(),
                'request' => [
                    'url' => $url,
                    'body' => $requestBody,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->textBeeApiKey,
                        'Content-Type' => 'application/json'
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('TextBee Test Error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    // Method to check device status
    public function checkDeviceStatus()
    {
        try {
            $url = $this->textBeeApiUrl . '/gateway/devices/' . $this->deviceId;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->textBeeApiKey,
                'Accept' => 'application/json'
            ])->get($url);
            
            Log::info('Device Status Response: ' . $response->body());
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'status' => 'success',
                    'device' => $data,
                    'device_active' => $data['active'] ?? false,
                    'device_status' => $data['status'] ?? 'unknown'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to check device status',
                    'response' => $response->body()
                ], $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('Error checking device status: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Normalize external API status values to DB-acceptable values.
     * This prevents MySQL enum/column truncation when providers return
     * values like "queued" that the database may not accept.
     */
    private function normalizeStatus($status)
    {
        $s = strtolower(trim((string) ($status ?? '')));

        // Allowed values we expect in the application/database
        $allowed = ['sent', 'failed', 'delivered', 'pending', 'processing'];

        if (in_array($s, $allowed, true)) {
            return $s;
        }

        // Map common external statuses to a safe DB value
        if ($s === 'queued' || $s === 'queued_for_processing') {
            return 'pending';
        }

        // Fallback to 'sent' to avoid DB insertion warnings/truncation
        return 'sent';
    }

}