<?php

namespace App\Http\Controllers;

use App\Services\EmailChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class EmailSenderController extends Controller
{
    protected $emailService;

    public function __construct(EmailChatService $emailService)
    {
        $this->emailService = $emailService;
    }

    protected function checkForDuplicate($emailData)
    {
        return DB::table('chattbl')
            ->where('sender_email', $emailData['sender_email'])
            ->where('receiver_email', $emailData['receiver_email'])
            ->where('subject', $emailData['subject'])
            ->where('message', $emailData['message'])
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();
    }

    /**
     * Send email from chat interface - FIXED VERSION
     */
    public function sendEmailFromChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            \Log::info("Sending email to: {$request->to_email}, Subject: {$request->subject}");

            $result = $this->emailService->sendEmailReply(
                $request->to_email,
                $request->subject,
                $request->message
            );

            if ($result['success']) {
                // Use current Manila time for consistent timestamps
                $now = Carbon::now('Asia/Manila');
                
                // Check if the new columns exist
                $columns = Schema::getColumnListing('chattbl');
                
                $insertData = [
                    'sender_id' => auth()->id(),
                    'sender_email' => auth()->user()->email,
                    'sender_name' => auth()->user()->name,
                    'receiver_email' => $request->to_email,
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'sender_role' => auth()->user()->role,
                    'message_type' => 'outgoing',
                    'created_at' => $now, // Use consistent Manila time
                    'updated_at' => $now, // Use consistent Manila time
                ];

                // Check for duplicate before inserting
                if ($this->checkForDuplicate($insertData)) {
                    \Log::warning("Duplicate message detected, skipping insert");
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Duplicate message detected'
                    ], 409);
                }

                // Store in database
                DB::table('chattbl')->insert($insertData);

                \Log::info("Email sent and saved to database successfully with Manila timestamp: {$now}");

                return response()->json([
                    'status' => 'success',
                    'message' => 'Email sent successfully'
                ]);
            } else {
                \Log::error("Email sending failed: " . ($result['error'] ?? 'Unknown error'));
                return response()->json([
                    'status' => 'error',
                    'message' => $result['error'] ?? 'Failed to send email'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Email sending error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}