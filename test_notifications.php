<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use App\Models\NotifApprovalAppointment;
use App\Models\User;
use App\Http\Controllers\ClientTableController;
use App\Http\Controllers\NotificationController;

echo "Testing notification insertion...\n";

$appointment = Appointment::where('appointment_approval', 'pending')->first();

if ($appointment) {
    echo "Found appointment ID: " . $appointment->id . "\n";

    $controller = new ClientTableController();

    try {
        $notification = $controller->insertApprovalNotification($appointment);
        echo "Notification created with ID: " . $notification->id . "\n";
        echo "Notification details: " . json_encode($notification->toArray()) . "\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No pending appointments found\n";
}

echo "Current notification count: " . NotifApprovalAppointment::count() . "\n";
echo "Current user notification count: " . App\Models\Notification::count() . "\n";
