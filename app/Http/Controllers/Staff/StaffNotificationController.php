<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffNotification;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StaffNotificationController extends Controller
{
    /**
     * Constructor - Apply auth middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all notifications for staff
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is staff or admin
            if (!$this->isStaffOrAdmin($user)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access. Staff or admin access required.',
                    'user_role' => $user->role ?? 'none'
                ], 403);
            }

            $perPage = $request->get('per_page', 20);
            
            $notifications = StaffNotification::where('staff_id', $user->id)
                ->orWhere('assigned_to', 'all') // Notifications assigned to all staff
                ->with('appointment')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => StaffNotification::where('staff_id', $user->id)
                    ->where('is_read', false)
                    ->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Staff Notification Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications for staff dashboard
     */
    public function getUnread(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is staff or admin
            if (!$this->isStaffOrAdmin($user)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access. Staff or admin access required.',
                    'user_role' => $user->role ?? 'none'
                ], 403);
            }

            $limit = $request->get('limit', 15);
            
            // Get notifications for this specific staff member AND notifications for all staff
            $notifications = StaffNotification::where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->with(['appointment', 'sender'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();

            // Format notifications for frontend
            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toDateTimeString(),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'appointment' => $notification->appointment,
                    'sender' => $notification->sender ? [
                        'name' => $notification->sender->name,
                        'email' => $notification->sender->email
                    ] : null
                ];
            });

            $unreadCount = StaffNotification::where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount,
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Staff Get Unread Notifications Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load notifications',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            if (!$this->isStaffOrAdmin($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $notification = StaffNotification::where('id', $id)
                ->where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'error' => 'Notification not found or you do not have permission'
                ], 404);
            }

            $notification->update(['is_read' => true]);

            // Get updated unread count
            $unreadCount = StaffNotification::where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('Mark Notification As Read Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for current staff
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$this->isStaffOrAdmin($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            StaffNotification::where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'unread_count' => 0
            ]);
        } catch (\Exception $e) {
            Log::error('Mark All Notifications As Read Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    /**
     * Get notification count for badge
     */
    public function getCount(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$this->isStaffOrAdmin($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $unreadCount = StaffNotification::where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Get Notification Count Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get notification count',
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Create test notification (for development)
     */
    public function createTestNotification(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$this->isStaffOrAdmin($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Create a test notification
            $notification = StaffNotification::create([
                'staff_id' => $user->id,
                'sender_id' => $user->id,
                'type' => 'test',
                'title' => 'Test Notification',
                'message' => 'This is a test notification for staff. Created at ' . now()->format('h:i A'),
                'assigned_to' => 'individual', // or 'all' for all staff
                'is_read' => false
            ]);

            // Get updated count
            $unreadCount = StaffNotification::where(function($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Test notification created successfully',
                'notification' => $notification,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('Create Test Notification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create notification for new pending appointment (called from Appointment model)
     */
    public static function createForPendingAppointment(Appointment $appointment, $staffId = null)
    {
        try {
            // If staffId is provided, create for specific staff
            if ($staffId) {
                $notification = StaffNotification::create([
                    'staff_id' => $staffId,
                    'sender_id' => $appointment->user_id ?? null,
                    'appointment_id' => $appointment->id,
                    'type' => 'pending_request',
                    'title' => 'New Appointment Request',
                    'message' => 'New appointment request from ' . ($appointment->fullname ?? 'Client') . 
                                ' for ' . ($appointment->selected_date ?? 'N/A') . 
                                ' at ' . ($appointment->selected_time ?? 'N/A'),
                    'assigned_to' => 'individual',
                    'is_read' => false
                ]);
                return $notification;
            }
            
            // Otherwise, create for all staff
            $staffUsers = User::where('role', 'staff')->get();
            
            $notifications = [];
            foreach ($staffUsers as $staff) {
                $notification = StaffNotification::create([
                    'staff_id' => $staff->id,
                    'sender_id' => $appointment->user_id ?? null,
                    'appointment_id' => $appointment->id,
                    'type' => 'pending_request',
                    'title' => 'New Appointment Request',
                    'message' => 'New appointment request from ' . ($appointment->fullname ?? 'Client') . 
                                ' for ' . ($appointment->selected_date ?? 'N/A') . 
                                ' at ' . ($appointment->selected_time ?? 'N/A'),
                    'assigned_to' => 'all',
                    'is_read' => false
                ]);
                $notifications[] = $notification;
            }
            
            return $notifications;
        } catch (\Exception $e) {
            Log::error('Create Pending Appointment Notification Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for appointment status update
     */
    public static function createForAppointmentUpdate(Appointment $appointment, $status, $staffId = null)
    {
        try {
            $statusMessages = [
                'approved' => 'Appointment has been approved',
                'denied' => 'Appointment has been denied',
                'rescheduled' => 'Appointment has been rescheduled',
                'completed' => 'Appointment has been completed'
            ];

            $message = $statusMessages[$status] ?? 'Appointment status updated';

            // If staffId is provided, create for specific staff
            if ($staffId) {
                return StaffNotification::create([
                    'staff_id' => $staffId,
                    'sender_id' => Auth::id() ?? null,
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_update',
                    'title' => 'Appointment Status Update',
                    'message' => $message . ' for appointment with ' . ($appointment->fullname ?? 'Client'),
                    'assigned_to' => 'individual',
                    'is_read' => false
                ]);
            }
            
            // Create for all staff
            $staffUsers = User::where('role', 'staff')->get();
            
            $notifications = [];
            foreach ($staffUsers as $staff) {
                $notifications[] = StaffNotification::create([
                    'staff_id' => $staff->id,
                    'sender_id' => Auth::id() ?? null,
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_update',
                    'title' => 'Appointment Status Update',
                    'message' => $message . ' for appointment with ' . ($appointment->fullname ?? 'Client'),
                    'assigned_to' => 'all',
                    'is_read' => false
                ]);
            }
            
            return $notifications;
        } catch (\Exception $e) {
            Log::error('Create Appointment Update Notification Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create system notification for all staff
     */
    public static function createSystemNotification($title, $message, $type = 'system')
    {
        try {
            $staffUsers = User::where('role', 'staff')->get();
            
            $notifications = [];
            foreach ($staffUsers as $staff) {
                $notifications[] = StaffNotification::create([
                    'staff_id' => $staff->id,
                    'sender_id' => Auth::id() ?? null,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'assigned_to' => 'all',
                    'is_read' => false
                ]);
            }
            
            return $notifications;
        } catch (\Exception $e) {
            Log::error('Create System Notification Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user is staff or admin
     */
    private function isStaffOrAdmin($user)
    {
        if (!$user) {
            return false;
        }
        
        $allowedRoles = ['staff', 'admin', 'superadmin', 'diffun_staff', 'cordon_staff'];
        return in_array($user->role, $allowedRoles);
    }
}