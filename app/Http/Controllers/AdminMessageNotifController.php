<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminMessageNotif;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\NewAdminMessageNotification;

class AdminMessageNotifController extends Controller
{
    /**
     * Get unread message notifications for admin
     */
    public function getUnread(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'notifications' => [],
                'unread_count' => 0
            ], 403);
        }

        try {
            $notifications = AdminMessageNotif::where('receiver_id', $admin->id)
                ->where('is_read', false)
                ->with(['sender:id,name,email'])
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'sender_name' => $notification->sender_name,
                        'sender_email' => $notification->sender_email,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->toDateTimeString()
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $notifications->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching message notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    public function createTestNotificationForDashboard(Request $request)
    {
        try {
            $admin = Auth::user();
            
            if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Create a test notification
            $notification = AdminMessageNotif::create([
                'type' => 'system_chat',
                'title' => 'Test Message Notification',
                'message' => 'This is a test message notification from the system. Created at: ' . now()->format('Y-m-d H:i:s'),
                'sender_id' => $admin->id,
                'sender_name' => $admin->name,
                'sender_email' => $admin->email,
                'receiver_id' => $admin->id,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification created successfully',
                'notification' => $notification,
                'unread_count' => AdminMessageNotif::where('receiver_id', $admin->id)
                    ->where('is_read', false)
                    ->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating test notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread count for message notifications
     */
    public function getUnreadCount(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $count = AdminMessageNotif::where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($id)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification = AdminMessageNotif::find($id);
        
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        // Check if notification belongs to current admin
        if ($notification->receiver_id != $admin->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'unread_count' => AdminMessageNotif::where('receiver_id', $admin->id)
                ->where('is_read', false)
                ->count()
        ]);
    }

    /**
     * Mark all notifications as read for current admin
     */
    public function markAllAsRead(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
     * Get all notifications for admin
     */
    public function getAll(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notifications = AdminMessageNotif::where('receiver_id', $admin->id)
            ->with(['sender:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => AdminMessageNotif::where('receiver_id', $admin->id)
                ->where('is_read', false)
                ->count()
        ]);
    }

    /**
     * Create a test notification (for debugging)
     */
    public function createTestNotification(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create a test notification
        $notification = AdminMessageNotif::create([
            'type' => 'system_chat',
            'title' => 'Test Message Notification',
            'message' => 'This is a test message notification from ' . $admin->name,
            'sender_id' => $admin->id,
            'sender_name' => $admin->name,
            'sender_email' => $admin->email,
            'receiver_id' => $admin->id,
            'message_id' => null,
            'is_read' => false,
        ]);

        // Broadcast the notification
        broadcast(new NewAdminMessageNotification($notification))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Test notification created',
            'notification' => $notification,
            'unread_count' => AdminMessageNotif::where('receiver_id', $admin->id)
                ->where('is_read', false)
                ->count()
        ]);
    }

    /**
     * Create notification from chat message
     */
    public function createFromChatMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string|in:system_chat,email,sms',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'sender_id' => 'required|integer|exists:users,id',
                'sender_name' => 'required|string|max:255',
                'sender_email' => 'required|email',
                'receiver_id' => 'required|integer|exists:users,id',
                'message_id' => 'nullable|integer|exists:chat_messages,id',
            ]);

            // Check if receiver is admin
            $receiver = User::find($validated['receiver_id']);
            if (!$receiver || !in_array($receiver->role, ['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver is not an admin'
                ], 400);
            }

            // Create notification
            $notification = AdminMessageNotif::create([
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'sender_id' => $validated['sender_id'],
                'sender_name' => $validated['sender_name'],
                'sender_email' => $validated['sender_email'],
                'receiver_id' => $validated['receiver_id'],
                'message_id' => $validated['message_id'] ?? null,
                'is_read' => false,
            ]);

            // Broadcast the notification
            broadcast(new NewAdminMessageNotification($notification))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'notification' => $notification
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}