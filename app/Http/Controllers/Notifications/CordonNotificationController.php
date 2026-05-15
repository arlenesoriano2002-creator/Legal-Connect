<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\StaffNotification;

class CordonNotificationController extends Controller
{
    /**
     * Get notifications for Cordon staff
     */
    public function getNotifications(Request $request)
    {
        try {
            $since = $request->input('since', Carbon::now()->subMinutes(60)->toIso8601String());
            $branch = $request->input('branch', 'cordon');
            
            // Convert since to Carbon instance
            $sinceDate = Carbon::parse($since);
            
            // Get pending appointments for Cordon branch created after $since
            // Use a permissive substring match so variants like "Cordon", "Cordon Branch",
            // or small typos/capitalization won't prevent notifications.
            $appointments = Appointment::whereRaw('LOWER(TRIM(selected_branch)) LIKE ?', ['%cordon%'])
                ->whereRaw('LOWER(TRIM(appointment_approval)) = ?', ['pending'])
                ->where('created_at', '>', $sinceDate)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                // select columns that actually exist on the appointments table
                ->get(['id', 'fullname', 'selected_date', 'selected_time', 'created_at', 'category', 'case_name']);
            
            $notifications = [];
            
            foreach ($appointments as $appointment) {
                $notifications[] = [
                    'id' => $appointment->id,
                    'type' => 'pending_appointment',
                    'title' => 'New Appointment Request',
                    'message' => "New appointment from {$appointment->fullname}",
                    'data' => [
                        'fullname' => $appointment->fullname,
                        'selected_date' => $appointment->selected_date,
                        'selected_time' => $appointment->selected_time,
                        // older schema uses category/case_name instead of purpose
                        'purpose' => $appointment->case_name ?? $appointment->category ?? null,
                    ],
                    'created_at' => $appointment->created_at->toIso8601String(),
                    'is_read' => false
                ];
            }

            if (Auth::check()) {
                $user = Auth::user();
                $staffNotifications = StaffNotification::where(function ($query) use ($user) {
                        $query->where('staff_id', $user->id)
                              ->orWhere('assigned_to', 'all');
                    })
                    ->where('is_read', false)
                    ->where('created_at', '>', $sinceDate)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                foreach ($staffNotifications as $notification) {
                    $notifications[] = [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'created_at' => $notification->created_at->toIso8601String(),
                        'is_read' => $notification->is_read,
                        'source' => 'staff_notification',
                    ];
                }
            }

            usort($notifications, function ($a, $b) {
                return strtotime($b['created_at']) <=> strtotime($a['created_at']);
            });
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications),
                'last_checked' => Carbon::now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('CordonNotificationController error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications'
            ], 500);
        }
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $user = Auth::user();
            $id = $request->input('id');
            $type = $request->input('type', 'appointment');

            if ($type === 'staff' || $type === 'message' || $type === 'message_inquiry') {
                $notification = StaffNotification::where('id', $id)
                    ->where(function ($query) use ($user) {
                        $query->where('staff_id', $user->id)
                              ->orWhere('assigned_to', 'all');
                    })
                    ->first();

                if (!$notification) {
                    return response()->json(['success' => false, 'error' => 'Notification not found'], 404);
                }

                $notification->update(['is_read' => true]);

                return response()->json(['success' => true]);
            }

            StaffNotification::where('appointment_id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'updated_at' => Carbon::now(),
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('CordonNotificationController::markRead error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllRead()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $user = Auth::user();

            StaffNotification::where(function ($query) use ($user) {
                    $query->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                })
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'updated_at' => Carbon::now(),
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('CordonNotificationController::markAllRead error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }
    
    /**
     * Create notification for new Cordon appointment
     */
    public static function createForPendingAppointment(Appointment $appointment)
    {
        try {
            $approval = isset($appointment->appointment_approval) ? strtolower(trim($appointment->appointment_approval)) : '';
            $branch = isset($appointment->selected_branch) ? strtolower(trim($appointment->selected_branch)) : '';
            
            if ($approval !== 'pending') {
                \Log::info("CordonNotificationController: skipping appointment {$appointment->id} - approval={$approval}");
                return false;
            }
            
            // Check if it's for Cordon branch using a substring check to tolerate variants
            if (stripos($branch, 'cordon') === false) {
                \Log::info("CordonNotificationController: skipping appointment {$appointment->id} - branch='{$appointment->selected_branch}' does not contain 'cordon'");
                return false;
            }
            
            // You could store this in a notifications table here
            // For now, we'll just return true since we query appointments directly
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('CordonNotificationController::createForPendingAppointment error: ' . $e->getMessage());
            return false;
        }
    }
}
