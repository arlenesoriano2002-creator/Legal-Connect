<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Models\AdminNotification;

class SendAdminNotification
{
    public function handle(AppointmentCreated $event)
    {
        AdminNotification::create([
            'type' => 'pending_request',
            'title' => 'New Appointment Request',
            'message' => 'New appointment request from ' . $event->appointment->fullname . ' for ' . $event->appointment->selected_date . ' at ' . $event->appointment->selected_time,
            'appointment_id' => $event->appointment->id
        ]);
    }
}