<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Appointment;
use App\Models\SmsMessage;
use App\Models\StaffNotification;
use App\Models\User;
use App\Http\Controllers\Admin\SmsChatController;
use App\Mail\AppointmentStatusMail;

class StaffPendingRequestsController extends Controller
{
    /**
     * Display pending appointments for Diffun Branch Office only
     */
    public function index()
    {
        // Check if user is staff (including secretary and clerk)
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch only Diffun Branch pending appointments
        $appointments = DB::table('appointments')
            ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
            ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Ensure persisted staff notifications exist for newly visible appointments
        try {
            $this->ensureNotificationsForAppointments($appointments);
        } catch (\Exception $e) {
            \Log::error('Error ensuring notifications for appointments: ' . $e->getMessage());
        }
        // Add branch filtering log
        \Log::info('Staff Pending Requests - User: ' . Auth::user()->name . ', Role: ' . Auth::user()->role . ', Count: ' . $appointments->count());

        return view('staff.StaffClientstbl', compact('appointments'));
    }

    /**
     * Get pending appointments data via AJAX
     */
    public function getPendingAppointments(Request $request)
    {
        // Check if user is staff (including secretary and clerk)
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointments = DB::table('appointments')
            ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
            ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Ensure persisted staff notifications exist for newly visible appointments
        try {
            $this->ensureNotificationsForAppointments($appointments);
        } catch (\Exception $e) {
            \Log::error('Error ensuring notifications for appointments (ajax): ' . $e->getMessage());
        }
        return response()->json([
            'success' => true,
            'appointments' => $appointments,
            'count' => $appointments->count()
        ]);
    }

    /**
     * Send email notification for appointment status using proper Mailable
     */
    private function isDiffunBranch($branch)
    {
        return str_contains(strtolower(trim($branch ?? '')), 'diffun');
    }

    private function sendAppointmentEmail($appointment, $status)
    {
        try {
            // Validate recipient
            $to = trim($appointment->email ?? '');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                \Log::warning('sendAppointmentEmail: invalid or empty recipient, skipping email', ['appointment_id' => $appointment->id, 'recipient' => $to]);
                return false;
            }

            // Log appointment data before sending
            \Log::info('sendAppointmentEmail: preparing to send', [
                'appointment_id' => $appointment->id,
                'status' => $status,
                'fullname' => $appointment->fullname,
                'email' => $to,
                'selected_date' => $appointment->selected_date ?? 'N/A',
                'selected_time' => $appointment->selected_time ?? 'N/A',
                'schedule_date' => $appointment->schedule_date ?? 'N/A',
                'schedule_time' => $appointment->schedule_time ?? 'N/A',
                'category' => $appointment->category,
                'case_name' => $appointment->case_name,
            ]);

            // Use the proper Mailable class for consistency with AppointmentController
            $mailable = new AppointmentStatusMail($appointment, $status);
            
            // Set From address with fallback
            $fromAddress = env('MAIL_FROM_ADDRESS') ?: env('MAIL_USERNAME') ?: config('mail.from.address');
            $fromName = env('MAIL_FROM_NAME') ?: config('mail.from.name') ?: 'LegalConnect';
            
            if ($fromAddress) {
                $mailable->from($fromAddress, $fromName);
            }
            
            // Send the email
            Mail::to($to)->send($mailable);
            
            $statusText = ($status === 'approved') ? 'Approval' : 'Denial';
            \Log::info("$statusText email sent to: " . $to . ' for appointment ID: ' . $appointment->id);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send email for appointment ID ' . $appointment->id . ': ' . $e->getMessage());
            \Log::error('Email Exception Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Approve an appointment
     */
    public function approve(\Illuminate\Http\Request $request, $id)
    {
        // Check if user is staff (including secretary and clerk)
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        // Check if appointment belongs to Diffun Branch Office
        if (! $this->isDiffunBranch($appointment->selected_branch)) {
            return response()->json(['error' => 'You can only manage Diffun Branch appointments'], 403);
        }

        // Update appointment status
        $appointment->appointment_approval = 'approved';
        $appointment->processed_by = Auth::user()->name ?? 'System';
        $appointment->save();

        // Prefer posted overrides for email/phone if provided by frontend
        $postedEmail = trim($request->input('email', '') ?? '');
        $postedPhone = trim($request->input('phone', '') ?? '');
        if ($postedEmail) {
            \Log::info('StaffPendingRequestsController: overriding recipient email from request payload', ['appointment_id' => $id, 'posted_email' => $postedEmail]);
            $appointment->email = $postedEmail;
        }
        if ($postedPhone) {
            \Log::info('StaffPendingRequestsController: overriding recipient phone from request payload', ['appointment_id' => $id, 'posted_phone' => $postedPhone]);
            $appointment->phone = $postedPhone;
        }

        // Send approval email
        $emailSent = $this->sendAppointmentEmail($appointment, 'approved');

        // Send SMS notification to client (if phone exists)
        $smsSent = false;
        $smsStatus = null;
        $smsResponse = null;
        $statusMessage = $this->buildAppointmentStatusMessage($appointment, 'approved');
        try {
            if (!empty($appointment->phone)) {
                $smsController = new SmsChatController();
                $smsText = $statusMessage;
                $smsResp = $smsController->sendViaIprog($appointment->phone, $smsText);
                $smsResponse = $smsResp;

                $savedStatus = 'failed';
                if (!empty($smsResp['success'])) {
                    $status = strtolower($smsResp['status'] ?? 'sent');
                    $savedStatus = ($status === 'queued') ? 'pending' : $status;
                    $smsSent = true;
                }

                $smsStatus = $savedStatus;

                SmsMessage::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => Auth::id(), // DB requires non-null; use staff id as placeholder
                    'phone_number' => $appointment->phone,
                    'message' => $smsText,
                    'message_type' => 'outgoing',
                    'status' => $savedStatus,
                    'message_id' => $smsResp['message_id'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS notification on approval: ' . $e->getMessage());
        }
        // Log the approval
        \Log::info('Appointment approved by staff', [
            'staff_id' => Auth::id(),
            'staff_name' => Auth::user()->name,
            'appointment_id' => $id,
            'client_name' => $appointment->fullname,
            'client_email' => $appointment->email,
            'email_sent' => $emailSent ? 'Yes' : 'No'
        ]);

        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
            'sms_status' => $smsStatus,
            'sms_response' => $smsResponse
        ]);
    }

    /**
     * Deny an appointment
     */
    public function deny($id, Request $request)
    {
        // Check if user is staff (including secretary and clerk)
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        // Check if appointment belongs to Diffun Branch Office
        if (! $this->isDiffunBranch($appointment->selected_branch)) {
            return response()->json(['error' => 'You can only manage Diffun Branch appointments'], 403);
        }

        // Update appointment status
        $appointment->appointment_approval = 'denied';
        $appointment->processed_by = Auth::user()->name ?? 'System';
        
        // Add denial reason if provided
        if ($request->has('reason')) {
            $appointment->denial_reason = $request->input('reason');
        }
        
        $appointment->save();

        // Send denial email
        $emailSent = $this->sendAppointmentEmail($appointment, 'denied');

        // Send SMS notification to client (if phone exists)
        $smsSent = false;
        $smsStatus = null;
        $smsResponse = null;
        $statusMessage = $this->buildAppointmentStatusMessage($appointment, 'denied');
        try {
            if (!empty($appointment->phone)) {
                $smsController = new SmsChatController();
                $reason = $request->input('reason', '');
                $smsText = $statusMessage;
                if (!empty($reason)) {
                    $smsText .= ' Reason: ' . $reason;
                }

                $smsResp = $smsController->sendViaIprog($appointment->phone, $smsText);
                $smsResponse = $smsResp;

                $savedStatus = 'failed';
                if (!empty($smsResp['success'])) {
                    $status = strtolower($smsResp['status'] ?? 'sent');
                    $savedStatus = ($status === 'queued') ? 'pending' : $status;
                    $smsSent = true;
                }

                $smsStatus = $savedStatus;

                SmsMessage::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => Auth::id(),
                    'phone_number' => $appointment->phone,
                    'message' => $smsText,
                    'message_type' => 'outgoing',
                    'status' => $savedStatus,
                    'message_id' => $smsResp['message_id'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS notification on denial: ' . $e->getMessage());
        }

        // Log the denial
        \Log::info('Appointment denied by staff', [
            'staff_id' => Auth::id(),
            'staff_name' => Auth::user()->name,
            'appointment_id' => $id,
            'client_name' => $appointment->fullname,
            'client_email' => $appointment->email,
            'reason' => $request->input('reason', 'No reason provided'),
            'email_sent' => $emailSent ? 'Yes' : 'No'
        ]);

        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
            'sms_status' => $smsStatus,
            'sms_response' => $smsResponse
        ]);
    }

    private function buildAppointmentStatusMessage($appointment, string $status): string
    {
        $caseName = trim((string) ($appointment->case_name ?? 'your selected case'));
        $category = trim((string) ($appointment->category ?? 'your selected category'));
        $status = strtolower(trim($status)) === 'denied' ? 'denied' : 'approved';
        $serviceFeeText = $this->getAppointmentServiceFeeText($appointment);

        return "Your appointment request for {$caseName} under {$category} has been {$status}. Service Fee: {$serviceFeeText}.";
    }

    private function getAppointmentServiceFeeText($appointment): string
    {
        $caseName = trim((string) ($appointment->case_name ?? ''));
        $category = trim((string) ($appointment->category ?? ''));

        if ($caseName === '' || $category === '') {
            return 'Not set yet';
        }

        $serviceFee = DB::table('case_categories')
            ->where('category', $category)
            ->where('case_name', $caseName)
            ->value('service_fee');

        if ($serviceFee === null || $serviceFee === '') {
            return 'Not set yet';
        }

        return "\u{20B1}" . number_format((float) $serviceFee, 2);
    }

    /**
     * Get appointment details for modal view
     */
    public function getAppointmentDetails($id)
    {
        // Check if user is staff (including secretary and clerk)
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        // Check if appointment belongs to Diffun Branch Office
        if (strtolower(trim($appointment->selected_branch ?? '')) !== 'diffun branch office') {
            return response()->json(['error' => 'You can only view Diffun Branch appointments'], 403);
        }

        // Debug: Log the image paths
        \Log::info('Appointment ID Images:', [
            'id' => $appointment->id,
            'id_front_raw' => $appointment->id_front,
            'id_back_raw' => $appointment->id_back,
            'id_front_type' => gettype($appointment->id_front),
            'id_back_type' => gettype($appointment->id_back)
        ]);

        // Convert image paths to full URLs
        $appointmentArray = $appointment->toArray();
        
        // Helper function to generate correct image URL
        function generateImageUrl($imagePath) {
            if (empty($imagePath) || $imagePath === 'null' || $imagePath === 'NULL') {
                return null;
            }
            
            // If it's already a full URL or base64, return as is
            if (strpos($imagePath, 'http') === 0 || strpos($imagePath, 'data:image') === 0) {
                return $imagePath;
            }
            
            // Check if it's just a filename (no path separators)
            if (strpos($imagePath, '/') === false && strpos($imagePath, '\\') === false) {
                // It's just a filename, prepend the storage path
                return asset('storage/ids/' . $imagePath);
            }
            
            // If it contains path separators but starts with storage or ids
            if (strpos($imagePath, 'storage') !== false || strpos($imagePath, 'ids') !== false) {
                // Remove any leading slashes or backslashes
                $cleanPath = ltrim($imagePath, '/\\');
                
                // If it starts with 'storage/', use asset()
                if (strpos($cleanPath, 'storage/') === 0) {
                    return asset($cleanPath);
                }
                
                // If it starts with 'ids/', prepend 'storage/'
                if (strpos($cleanPath, 'ids/') === 0) {
                    return asset('storage/' . $cleanPath);
                }
                
                // Otherwise, assume it's relative to storage/ids
                return asset('storage/ids/' . $cleanPath);
            }
            
            // Fallback: try to construct URL from the raw path
            return asset('storage/ids/' . basename($imagePath));
        }
        
        // Generate URLs for both ID images
        $appointmentArray['id_front'] = generateImageUrl($appointment->id_front);
        $appointmentArray['id_back'] = generateImageUrl($appointment->id_back);
        
        // Log the generated URLs for debugging
        \Log::info('Generated Image URLs:', [
            'id_front_url' => $appointmentArray['id_front'],
            'id_back_url' => $appointmentArray['id_back']
        ]);

        return response()->json([
            'success' => true,
            'appointment' => $appointmentArray
        ]);
    }

    /**
     * Get statistics for pending requests
     */
    public function getStatistics()
    {
        // Check if user is staff (including secretary and clerk)
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalPending = DB::table('appointments')
            ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
            ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
            ->count();

        $todayPending = DB::table('appointments')
            ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
            ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
            ->whereDate('created_at', today())
            ->count();

        $byCategory = DB::table('appointments')
            ->select('category', DB::raw('COUNT(*) as count'))
            ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
            ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_pending' => $totalPending,
                'today_pending' => $todayPending,
                'by_category' => $byCategory
            ]
        ]);
    }

    /**
     * Ensure StaffNotification rows exist for the provided appointments.
     * Creates notifications for users with roles 'staff' and 'diffun_staff' if none exist
     * for the given appointment id to avoid duplicates.
     */
    private function ensureNotificationsForAppointments($appointments)
    {
        if (empty($appointments) || count($appointments) === 0) return;

        $roles = ['staff', 'diffun_staff'];
        $staffUsers = User::whereIn('role', $roles)->get();

        foreach ($appointments as $a) {
            $apptId = $a->id ?? null;
            if (!$apptId) continue;

            // Create notifications for each staff user if one doesn't already exist for that staff+appointment
            foreach ($staffUsers as $staff) {
                try {
                    $exists = StaffNotification::where('appointment_id', $apptId)
                        ->where('staff_id', $staff->id)
                        ->exists();
                    if ($exists) continue;

                    StaffNotification::create([
                        'staff_id' => $staff->id,
                        'sender_id' => null,
                        'appointment_id' => $apptId,
                        'type' => 'pending_request',
                        'title' => 'New Appointment Request',
                        'message' => 'New appointment request from ' . ($a->fullname ?? 'Client') . ' for ' . ($a->selected_date ?? 'N/A') . ' at ' . ($a->selected_time ?? 'N/A'),
                        'assigned_to' => 'all',
                        'is_read' => false
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to create StaffNotification for appointment ' . $apptId . ' user ' . $staff->id . ' - ' . $e->getMessage());
                }
            }
        }
    }
}
