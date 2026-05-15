<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Models\AdminMessageNotif;
use App\Models\Appointment;
use App\Models\User;
use App\Helpers\PerTabAuthHelper;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    /**
     * Resolve the current admin user across the app's mixed auth setup.
     */
    private function resolveAdminUser(): ?User
    {
        $user = PerTabAuthHelper::getTabUser();

        if (!$user) {
            $user = Auth::guard('admin')->user() ?? Auth::user();
        }

        if (!$user instanceof User) {
            return null;
        }

        return in_array($user->role, ['admin', 'superadmin']) ? $user : null;
    }

    private function unauthorizedResponse()
    {
        return response()->json([
            'success' => false,
            'error' => 'Unauthorized',
            'notifications' => [],
            'unread_count' => 0,
        ], 403);
    }

    /**
     * Get all notifications for admin
     */
    public function index(Request $request)
    {
        $admin = $this->resolveAdminUser();

        if (!$admin) {
            return $this->unauthorizedResponse();
        }

        $notifications = AdminNotification::with('appointment')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => AdminNotification::getUnreadCount()
        ]);
    }

    /**
     * Get unread notifications - UPDATED to include message notifications
     */
    public function getUnread(Request $request)
    {
        try {
            $admin = $this->resolveAdminUser();

            if (!$admin) {
                return $this->unauthorizedResponse();
            }

            // Get appointment notifications
            $appointmentNotifications = AdminNotification::where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notification) {
                return [
                    'id' => 'appointment_' . $notification->id,
                    'type' => 'appointment',
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toISOString(),
                    'is_read' => $notification->is_read,
                    'redirect_url' => '/clientstbl'
                ];
            });

        // Get message notifications
        $messageNotifications = AdminMessageNotif::where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $redirectUrl = '/admin/system-chat'; // default
                $iconType = 'comments'; // default

                // Determine redirect URL and icon based on message type
                switch ($notification->type) {
                    case 'email':
                        $redirectUrl = '/email-chat';
                        $iconType = 'envelope';
                        break;
                    case 'sms':
                        $redirectUrl = '/admin/sms-chat';
                        $iconType = 'sms';
                        break;
                    case 'system':
                        $redirectUrl = '/admin/system-chat';
                        $iconType = 'comments';
                        break;
                }

                return [
                    'id' => 'message_' . $notification->id,
                    'type' => 'message',
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toISOString(),
                    'is_read' => $notification->is_read,
                    'sender_name' => $notification->sender_name,
                    'sender_email' => $notification->sender_email,
                    'redirect_url' => $redirectUrl,
                    'icon_type' => $iconType
                ];
            });

        // Merge and sort all notifications by created_at desc
        $allNotifications = collect();
        
        // Add appointment notifications if any exist
        if ($appointmentNotifications && $appointmentNotifications->isNotEmpty()) {
            $allNotifications = $allNotifications->merge($appointmentNotifications);
        }
        
        // Add message notifications if any exist
        if ($messageNotifications && $messageNotifications->isNotEmpty()) {
            $allNotifications = $allNotifications->merge($messageNotifications);
        }
        
        // Sort by created_at desc and take 15
        $allNotifications = $allNotifications->sortByDesc(function ($item) {
            return strtotime($item['created_at']);
        })->take(15)->values();

        return response()->json([
            'success' => true,
            'notifications' => $allNotifications->toArray(),
            'unread_count' => $allNotifications->where('is_read', false)->count()
        ]);
        } catch (\Exception $e) {
            \Log::error('Error in getUnread: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read - UPDATED to handle both appointment and message notifications
     */
    public function markAsRead($id)
    {
        $admin = $this->resolveAdminUser();

        if (!$admin) {
            return $this->unauthorizedResponse();
        }

        // Check if it's an appointment notification (starts with 'appointment_')
        if (str_starts_with($id, 'appointment_')) {
            $notificationId = str_replace('appointment_', '', $id);
            $notification = AdminNotification::find($notificationId);

            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            $notification->markAsRead();
        }
        // Check if it's a message notification (starts with 'message_')
        elseif (str_starts_with($id, 'message_')) {
            $notificationId = str_replace('message_', '', $id);
            $notification = AdminMessageNotif::where('id', $notificationId)
                ->where('receiver_id', $admin->id)
                ->first();

            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            $notification->update(['is_read' => true]);
        }
        else {
            return response()->json(['error' => 'Invalid notification ID format'], 400);
        }

        // Get updated unread count from both tables
        $appointmentUnreadCount = AdminNotification::where('is_read', false)->count();
        $messageUnreadCount = AdminMessageNotif::where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->count();
        $totalUnreadCount = $appointmentUnreadCount + $messageUnreadCount;

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'unread_count' => $totalUnreadCount
        ]);
    }

    /**
     * Mark all notifications as read - UPDATED to handle both appointment and message notifications
     */
    public function markAllAsRead(Request $request)
    {
        $admin = $this->resolveAdminUser();

        if (!$admin) {
            return $this->unauthorizedResponse();
        }

        // Mark all appointment notifications as read
        AdminNotification::where('is_read', false)->update(['is_read' => true]);

        // Mark all message notifications as read for this admin
        AdminMessageNotif::where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    }

    /**
     * Create notification for new pending appointment
     */
   public static function createForPendingAppointment(Appointment $appointment)
{
    $notification = AdminNotification::create([
        'type' => 'pending_request',
        'title' => 'New Appointment Request',
        'message' => 'New appointment request from ' . $appointment->fullname . ' for ' . $appointment->selected_date . ' at ' . $appointment->selected_time,
        'appointment_id' => $appointment->id
    ]);

    return $notification;
}

    /**
     * Get notification count (for badge) - UPDATED to include both appointment and message notifications
     */
    public function getCount(Request $request)
    {
        $admin = $this->resolveAdminUser();

        if (!$admin) {
            return $this->unauthorizedResponse();
        }

        // Count unread appointment notifications
        $appointmentUnreadCount = AdminNotification::where('is_read', false)->count();

        // Count unread message notifications for this admin
        $messageUnreadCount = AdminMessageNotif::where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->count();

        $totalUnreadCount = $appointmentUnreadCount + $messageUnreadCount;

        return response()->json([
            'success' => true,
            'unread_count' => $totalUnreadCount
        ]);
    }
}
