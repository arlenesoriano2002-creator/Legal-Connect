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
            'to_email' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
            'attachments.*' => 'file'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            \Log::info("Sending email to: {$request->to_email}, Subject: {$request->subject}");

            // Prepare attachments if any
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (!$file->isValid()) continue;
                    $content = base64_encode(file_get_contents($file->getRealPath()));
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getClientMimeType(),
                        'base64' => $content
                    ];
                }
            }

            $result = $this->emailService->sendEmailReply(
                $request->to_email,
                $request->subject,
                $request->message,
                $request->message, // use same as HTMLPart for now
                $attachments
            );

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email sent successfully',
                    'response' => $result['response'] ?? null
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