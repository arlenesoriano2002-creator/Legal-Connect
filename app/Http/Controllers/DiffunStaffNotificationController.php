<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\StaffNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DiffunStaffNotificationController extends Controller
{
    /**
     * Return new users and new pending Diffun appointments since provided timestamp.
     * Query param: since (ISO8601 string)
     */
    public function getNotifications(Request $request)
    {
        $since = $request->query('since');

        try {
            $sinceTime = $since ? Carbon::parse($since) : Carbon::now()->subMinutes(60);
        } catch (\Exception $e) {
            $sinceTime = Carbon::now()->subMinutes(60);
        }

                // Note: new user notifications are intentionally omitted for Diffun staff
                $newUsers = collect();

        // New appointments with pending status for Diffun Branch Office
        try {
            $newAppointments = Appointment::where('created_at', '>', $sinceTime)
                ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
                ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(['id','fullname','selected_date','selected_time','created_at']);
        } catch (\Exception $e) {
            \Log::error('DiffunStaffNotificationController: error querying appointments - ' . $e->getMessage());
            $newAppointments = collect();
        }
        // Also include persisted StaffNotification rows (unread) for authenticated user
        $staffNotifications = collect();
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $staffNotifications = StaffNotification::with('appointment', 'sender')
                    ->where(function($q) use ($user) {
                        $q->where('staff_id', $user->id)
                          ->orWhere('assigned_to', 'all');
                    })
                    ->where('is_read', false)
                    ->where('created_at', '>', $sinceTime)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (\Exception $e) {
            \Log::error('DiffunStaffNotificationController: error querying StaffNotification - ' . $e->getMessage());
            $staffNotifications = collect();
        }

        // Map staff notifications to a lightweight unified shape so the client can render them
        $mappedStaff = $staffNotifications->map(function($notif){
            $appt = $notif->appointment;
            return [
                'notification_id' => $notif->id,
                'source' => 'staff_notification',
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'created_at' => $notif->created_at,
                'appointment' => $appt ? [
                    'id' => $appt->id,
                    'fullname' => $appt->fullname,
                    'selected_date' => $appt->selected_date,
                    'selected_time' => $appt->selected_time,
                    'created_at' => $appt->created_at
                ] : null
            ];
        });

        \Log::info('DiffunStaffNotificationController: found ' . $newAppointments->count() . ' new appointments and ' . $mappedStaff->count() . ' staff notifications since ' . $sinceTime->toIso8601String());

        return response()->json([
            'success' => true,
            'since' => $sinceTime->toIso8601String(),
            // intentionally do not include new_users
            'new_appointments' => $newAppointments,
            'staff_notifications' => $mappedStaff,
            'counts' => [
                'appointments' => $newAppointments->count()
            ]
        ]);
    }

    /**
     * Mark a single notification as read.
     * This endpoint currently acts as an acknowledgement hook and returns success.
     */
    public function markRead(Request $request)
    {
        // Accept an id and optional type. If type == 'staff', mark the StaffNotification as read.
        $id = $request->input('id');
        $type = $request->input('type', 'appointment');
        \Log::info('DiffunStaffNotificationController::markRead - id=' . ($id ?? 'null') . ' type=' . $type);

        if ($type === 'staff') {
            try {
                $notif = StaffNotification::find($id);
                if ($notif) {
                    $notif->is_read = true;
                    $notif->save();
                    return response()->json(['success' => true]);
                }
                return response()->json(['success' => false, 'error' => 'Staff notification not found'], 404);
            } catch (\Exception $e) {
                \Log::error('DiffunStaffNotificationController::markRead error marking staff notification - ' . $e->getMessage());
                return response()->json(['success' => false, 'error' => 'Server error'], 500);
            }
        }

        // For appointment-type marks we should persist read-state by marking any
        // existing StaffNotification for this appointment as read for the auth user
        // (or assigned_to='all'). If none exists, create one marked as read so it
        // won't reappear after refresh.
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }
            $user = Auth::user();
            $apptId = $id;

            // Update any existing notifications for this appointment for this user / assigned_to all
            $updated = StaffNotification::where('appointment_id', $apptId)
                ->where(function($q) use ($user) {
                    $q->where('staff_id', $user->id)
                      ->orWhere('assigned_to', 'all');
                })->where('is_read', false)
                ->update(['is_read' => true, 'updated_at' => Carbon::now()]);

            if (!$updated) {
                // No existing row - create a read notification record so it won't appear
                try {
                    StaffNotification::create([
                        'staff_id' => $user->id,
                        'sender_id' => null,
                        'appointment_id' => $apptId,
                        'type' => 'pending_request',
                        'title' => 'Appointment acknowledged',
                        'message' => 'Appointment ' . $apptId . ' acknowledged by ' . ($user->name ?? 'staff'),
                        'assigned_to' => 'all',
                        'is_read' => true
                    ]);
                } catch (\Exception $e) {
                    // If create fails (e.g., unique constraint), log and continue
                    \Log::warning('DiffunStaffNotificationController::markRead failed to create ack notification - ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('DiffunStaffNotificationController::markRead error handling appointment mark - ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        \Log::info('DiffunStaffNotificationController::markAllRead');
        try {
            if (Auth::check()) {
                $user = Auth::user();
                // Mark persisted staff notifications for this user (or assigned to all) as read
                StaffNotification::where(function($q) use ($user) {
                    $q->where('staff_id', $user->id)
                      ->orWhere('assigned_to', 'all');
                })->where('is_read', false)->update(['is_read' => true, 'updated_at' => Carbon::now()]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('DiffunStaffNotificationController::markAllRead error - ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }
}
