<?php

namespace App\Http\Controllers;

use App\Models\ConcernsInquiriesMessage;
use App\Models\StaffNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageReceived;

class ConcernsInquiriesController extends Controller
{
    /**
     * Display a listing of the messages.
     */
    public function index()
    {
        $messages = ConcernsInquiriesMessage::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contact');
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:5000',
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'message.required' => 'Please enter your message.',
            'message.min' => 'Your message must be at least 10 characters.',
            'message.max' => 'Your message cannot exceed 5000 characters.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        try {
            // Create the message
            $message = ConcernsInquiriesMessage::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'status' => 'unread',
            ]);

            $this->createStaffInquiryNotifications($message);

            // Send email notification to admin (optional)
            if (config('mail.enable_contact_notifications', true)) {
                $this->sendNotificationEmail($message);
            }

            // Log the submission
            Log::info('New contact message received', [
                'id' => $message->id,
                'email' => $message->email,
                'name' => $message->name,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Thank you for your message! We have received it and will get back to you soon.');

        } catch (\Exception $e) {
            Log::error('Error storing contact message: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Sorry, there was an error submitting your message. Please try again later.');
        }
    }

    /**
     * Display the specified message.
     */
    public function show($id)
    {
        $message = ConcernsInquiriesMessage::findOrFail($id);
        
        // Mark as read when viewing
        if ($message->status == 'unread') {
            $message->markAsRead();
        }
        
        return view('admin.messages.show', compact('message'));
    }

    /**
     * Update the status of a message.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:unread,read,replied,pending'
        ]);

        $message = ConcernsInquiriesMessage::findOrFail($id);
        $message->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $message->status
        ]);
    }

    /**
     * Delete a message.
     */
    public function destroy($id)
    {
        $message = ConcernsInquiriesMessage::findOrFail($id);
        $message->delete();

        return redirect()
            ->route('messages.index')
            ->with('success', 'Message deleted successfully.');
    }

    /**
     * Send notification email to admin.
     */
    private function sendNotificationEmail(ConcernsInquiriesMessage $message)
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@legalconnect.com');
            
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactMessageReceived($message));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send contact notification email: ' . $e->getMessage());
        }
    }

    /**
     * Create unread inquiry notifications for staff users.
     */
    private function createStaffInquiryNotifications(ConcernsInquiriesMessage $message): void
    {
        try {
            $staffUsers = User::whereIn('role', ['staff', 'diffun_staff', 'cordon_staff'])
                ->get(['id']);

            if ($staffUsers->isEmpty()) {
                return;
            }

            $subject = trim((string) ($message->subject ?? ''));
            $bodyPreview = trim((string) $message->message);
            $bodyPreview = mb_substr($bodyPreview, 0, 90) . (mb_strlen($bodyPreview) > 90 ? '...' : '');

            foreach ($staffUsers as $staffUser) {
                StaffNotification::create([
                    'staff_id' => $staffUser->id,
                    'sender_id' => null,
                    'appointment_id' => null,
                    'type' => 'message_inquiry',
                    'title' => 'New Message Inquiry',
                    'message' => $subject !== ''
                        ? "{$message->name} sent a new inquiry: {$subject}"
                        : "{$message->name} sent a new inquiry. {$bodyPreview}",
                    'assigned_to' => 'individual',
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create inquiry staff notifications: ' . $e->getMessage(), [
                'inquiry_email' => $message->email ?? null,
            ]);
        }
    }

    /**
     * Get message statistics.
     */
    public function statistics()
    {
        $total = ConcernsInquiriesMessage::count();
        $unread = ConcernsInquiriesMessage::unread()->count();
        $read = ConcernsInquiriesMessage::read()->count();
        $replied = ConcernsInquiriesMessage::where('status', 'replied')->count();
        $pending = ConcernsInquiriesMessage::pending()->count();

        return response()->json([
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'replied' => $replied,
            'pending' => $pending
        ]);
    }
}
