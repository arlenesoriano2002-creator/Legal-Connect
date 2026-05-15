<?php

namespace App\Traits;

use App\Models\StaffNotification;
use App\Models\Appointment;
use App\Http\Controllers\StaffNotificationController;

trait CreatesStaffNotifications
{
    /**
     * Create a notification when a new appointment is made
     */
    public static function bootCreatesStaffNotifications()
    {
        static::created(function ($appointment) {
            if ($appointment instanceof Appointment && $appointment->appointment_approval === 'pending') {
                StaffNotificationController::createForPendingAppointment($appointment);
            }
        });

        static::updated(function ($appointment) {
            if ($appointment instanceof Appointment && $appointment->isDirty('appointment_approval')) {
                $oldStatus = $appointment->getOriginal('appointment_approval');
                $newStatus = $appointment->appointment_approval;
                
                // Only notify for status changes from pending
                if ($oldStatus === 'pending' && in_array($newStatus, ['approved', 'denied'])) {
                    StaffNotificationController::createForAppointmentUpdate($appointment, $newStatus);
                }
            }
        });
    }
}