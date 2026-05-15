<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Appointment;

class AppointmentNotificationController extends Controller
{
    /**
     * Create notifications for newly created or updated appointments when they
     * meet the criteria for staff notifications (pending + Cordon Branch Office).
     *
     * @param Appointment $appointment
     * @return bool
     */
    public static function createForPendingAppointment(Appointment $appointment)
    {
        try {
            $approval = isset($appointment->appointment_approval) ? strtolower(trim($appointment->appointment_approval)) : '';
            $branch = isset($appointment->selected_branch) ? strtolower(trim($appointment->selected_branch)) : '';
            if ($approval !== 'pending') {
                Log::info("AppointmentNotification: skipping appointment {$appointment->id} - approval={$approval}");
                return false;
            }

            // Normalize common variants of branch name and be permissive
            if (stripos($branch, 'cordon') !== false) {
                Log::info("AppointmentNotification: appointment {$appointment->id} matched Cordon branch ('{$appointment->selected_branch}'), delegating to CordonNotificationController");
                return \App\Http\Controllers\Notifications\CordonNotificationController::createForPendingAppointment($appointment);
            }

            if (stripos($branch, 'diffun') !== false) {
                Log::info("AppointmentNotification: appointment {$appointment->id} matched Diffun branch ('{$appointment->selected_branch}'), delegating to Diffun handler");
                return \App\Http\Controllers\Staff\StaffNotificationController::createForPendingAppointment($appointment);
            }

            Log::info("AppointmentNotification: skipping appointment {$appointment->id} - branch did not match (branch='{$appointment->selected_branch}')");
            return false;
        } catch (\Exception $e) {
            Log::error('AppointmentNotificationController::createForPendingAppointment error: ' . $e->getMessage());
            return false;
        }
    }
}