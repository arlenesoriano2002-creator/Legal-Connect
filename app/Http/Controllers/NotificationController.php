<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Appointment;
use App\Models\NotifApprovalAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public static function createNotification($userId, $type, $message)
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public function getNotifications()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Store notification when appointment status changes
    public static function storeApprovalNotification(Appointment $appointment)
    {
        NotifApprovalAppointment::create([
            'fullname' => $appointment->fullname,
            'email' => $appointment->email,
            'appointment_approval' => $appointment->appointment_approval,
            'appointment_date' => $appointment->appointment_date,
            'appointment_time' => $appointment->appointment_time,
        ]);
    }

    // Get all approval notifications for logged-in user
    public function getUserApprovalNotifications()
    {
        $user = Auth::user();
        
        $notifications = NotifApprovalAppointment::where('email', $user->email)
            ->orWhere('fullname', $user->name)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    // Get appointment status message for NotifApprovalAppointment
    private function getApprovalStatusMessage($notification)
    {
        $status = strtolower($notification->appointment_approval);
        $fullname = $notification->fullname;
        $date = $notification->appointment_date;
        $time = $notification->appointment_time;
        
        $datetime = '';
        if ($date) {
            $datetime = " scheduled on " . $date->format('M d, Y');
            if ($time) {
                $datetime .= " at " . $time;
            }
        }
        
        switch ($status) {
            case 'approved':
                return "🎉 Your appointment request for $fullname has been approved!$datetime";
            case 'denied':
                return "❌ Your appointment request for $fullname has been denied.$datetime";
            case 'pending':
                return "⏳ Your appointment request for $fullname is pending review.$datetime";
            default:
                return "📅 Appointment status updated for $fullname: " . $notification->approval_appointment . $datetime;
        }
    }

    // Get appointment status message for Appointment model (legacy method)
    private function getAppointmentStatusMessage($appointment)
    {
        $status = strtolower($appointment->appointment_approval);
        $fullname = $appointment->fullname;
        
        switch ($status) {
            case 'approved':
                return "Your appointment request for $fullname has been approved!";
            case 'denied':
                return "Your appointment request for $fullname has been denied.";
            case 'pending':
                return "Your appointment request for $fullname is pending review.";
            default:
                return "Appointment status updated for $fullname: " . $appointment->appointment_approval;
        }
    }

    // Legacy method - kept for backward compatibility
    public function getUserAppointmentNotifications()
    {
        $user = Auth::user();
        
        // Get appointments for the current user (using email or name)
        $appointments = Appointment::where('email', $user->email)
            ->orWhere('fullname', $user->name)
            ->orderBy('updated_at', 'desc')
            ->get();

        $notifications = [];
        
        foreach ($appointments as $appointment) {
            // Create notification message based on appointment status
            $message = $this->getAppointmentStatusMessage($appointment);
            
            $notifications[] = [
                'id' => $appointment->id,
                'message' => $message,
                'appointment_approval' => $appointment->appointment_approval,
                'created_at' => $appointment->updated_at,
                'is_read' => false
            ];
        }

        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }
}