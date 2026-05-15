<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Mail\AppointmentStatusMail;
use App\Models\SmsMessage;
use App\Http\Controllers\Admin\SmsChatController;
use DB;

class MessageInquiriesController extends Controller
{
    /**
     * Display message inquiries for Diffun staff
     */
    public function diffunIndex()
    {
        $inquiries = DB::table('concerns_inquiries_message')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.message_inquiries', compact('inquiries'));
    }

    /**
     * Display message inquiries for Cordon staff
     */
    public function cordonIndex()
    {
        $inquiries = DB::table('concerns_inquiries_message')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cordon_staff.message_inquiries', compact('inquiries'));
    }

    /**
     * Get message inquiries via AJAX for dynamic loading
     */
    public function getInquiries(Request $request)
    {
        try {
            $inquiries = DB::table('concerns_inquiries_message')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'inquiries' => $inquiries,
                'count' => $inquiries->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inquiries: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to fetch inquiries'], 500);
        }
    }

    /**
     * Send email reply to inquiry
     */
    public function sendEmailReply(Request $request)
    {
        try {
            $request->validate([
                'inquiry_id' => 'required|numeric',
                'email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string'
            ]);

            $inquiry = DB::table('concerns_inquiries_message')
                ->where('id', $request->inquiry_id)
                ->first();

            if (!$inquiry) {
                return response()->json(['success' => false, 'error' => 'Inquiry not found'], 404);
            }

            $to = $request->email;

            // Validate email
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['success' => false, 'error' => 'Invalid email address'], 422);
            }

            try {
                // Send email using raw Mail facade with proper configuration
                $fromAddress = env('MAIL_FROM_ADDRESS') ?: env('MAIL_USERNAME') ?: config('mail.from.address');
                $fromName = env('MAIL_FROM_NAME') ?: config('mail.from.name') ?: 'LegalConnect';

                Mail::send('emails.inquiry_reply', [
                    'inquiry_name' => $inquiry->name,
                    'reply_subject' => $request->subject,
                    'reply_message' => $request->message,
                    'sender_name' => Auth::user()->name
                ], function ($message) use ($to, $fromAddress, $fromName, $request) {
                    $message->to($to)
                            ->subject('Re: ' . $request->subject)
                            ->from($fromAddress, $fromName);
                });

                Log::info('Inquiry email reply sent', [
                    'inquiry_id' => $request->inquiry_id,
                    'to' => $to,
                    'subject' => $request->subject,
                    'sent_by' => Auth::user()->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Email sent successfully'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send inquiry email: ' . $e->getMessage());
                return response()->json(['success' => false, 'error' => 'Failed to send email: ' . $e->getMessage()], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Send SMS reply to inquiry
     */
    public function sendSmsReply(Request $request)
    {
        try {
            $request->validate([
                'inquiry_id' => 'required|numeric',
                'phone' => 'required|string|max:20',
                'message' => 'required|string|max:160'
            ]);

            $inquiry = DB::table('concerns_inquiries_message')
                ->where('id', $request->inquiry_id)
                ->first();

            if (!$inquiry) {
                return response()->json(['success' => false, 'error' => 'Inquiry not found'], 404);
            }

            $phone = $request->phone;
            $smsText = $request->message;

            try {
                $smsController = new SmsChatController();
                $smsResp = $smsController->sendViaIprog($phone, $smsText);
                
                $smsSent = false;
                $savedStatus = 'failed';
                
                if (!empty($smsResp['success'])) {
                    $status = strtolower($smsResp['status'] ?? 'sent');
                    $savedStatus = ($status === 'queued') ? 'pending' : $status;
                    $smsSent = true;
                }

                // Log SMS message
                SmsMessage::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => Auth::id(),
                    'phone_number' => $phone,
                    'message' => $smsText,
                    'message_type' => 'outgoing',
                    'status' => $savedStatus,
                    'message_id' => $smsResp['message_id'] ?? null
                ]);

                Log::info('Inquiry SMS reply sent', [
                    'inquiry_id' => $request->inquiry_id,
                    'phone' => $phone,
                    'status' => $savedStatus,
                    'sent_by' => Auth::user()->name
                ]);

                if ($smsSent) {
                    return response()->json([
                        'success' => true,
                        'message' => 'SMS sent successfully'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to send SMS: ' . ($smsResp['error'] ?? 'Unknown error')
                    ], 500);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send inquiry SMS: ' . $e->getMessage());
                return response()->json(['success' => false, 'error' => 'Failed to send SMS: ' . $e->getMessage()], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Delete a message inquiry
     */
    public function destroy($id)
    {
        try {
            $inquiry = DB::table('concerns_inquiries_message')
                ->where('id', $id)
                ->first();

            if (!$inquiry) {
                return response()->json([
                    'success' => false,
                    'error' => 'Inquiry not found'
                ], 404);
            }

            DB::table('concerns_inquiries_message')
                ->where('id', $id)
                ->delete();

            Log::info('Message inquiry deleted', [
                'inquiry_id' => $id,
                'deleted_by' => Auth::user()->name ?? 'Unknown',
                'deleted_by_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inquiry deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete inquiry: ' . $e->getMessage(), [
                'inquiry_id' => $id,
                'deleted_by_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete inquiry'
            ], 500);
        }
    }
}
