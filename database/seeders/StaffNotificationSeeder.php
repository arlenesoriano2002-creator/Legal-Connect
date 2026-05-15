<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffNotification;
use App\Models\User;
use App\Models\Appointment;

class StaffNotificationSeeder extends Seeder
{
    public function run()
    {
        // Get all staff users
        $staffUsers = User::where('role', 'staff')->get();
        
        // Get some appointments
        $appointments = Appointment::limit(10)->get();
        
        // Create notifications for each staff
        foreach ($staffUsers as $staff) {
            // Create some individual notifications
            StaffNotification::factory()
                ->count(5)
                ->create([
                    'staff_id' => $staff->id,
                    'assigned_to' => 'individual',
                ]);
            
            // Create some appointment notifications
            foreach ($appointments->take(3) as $appointment) {
                StaffNotification::create([
                    'staff_id' => $staff->id,
                    'sender_id' => $appointment->user_id,
                    'appointment_id' => $appointment->id,
                    'type' => 'pending_request',
                    'title' => 'New Appointment Request',
                    'message' => 'New appointment request from ' . $appointment->fullname . 
                                ' for ' . $appointment->selected_date . 
                                ' at ' . $appointment->selected_time,
                    'assigned_to' => 'individual',
                    'is_read' => rand(0, 1) === 1,
                ]);
            }
        }
        
        // Create some notifications for all staff
        foreach ($appointments->take(2) as $appointment) {
            foreach ($staffUsers as $staff) {
                StaffNotification::create([
                    'staff_id' => $staff->id,
                    'sender_id' => 1, // Admin user
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_update',
                    'title' => 'Appointment Approved',
                    'message' => 'Appointment with ' . $appointment->fullname . ' has been approved',
                    'assigned_to' => 'all',
                    'is_read' => false,
                ]);
            }
        }
        
        // Create system notifications for all staff
        $systemMessages = [
            'System maintenance scheduled for tonight at 11 PM',
            'New feature released: Appointment analytics dashboard',
            'Updated office hours for next week',
            'Client feedback survey now available',
        ];
        
        foreach ($systemMessages as $message) {
            foreach ($staffUsers as $staff) {
                StaffNotification::create([
                    'staff_id' => $staff->id,
                    'sender_id' => 1, // Admin user
                    'type' => 'system',
                    'title' => 'System Announcement',
                    'message' => $message,
                    'assigned_to' => 'all',
                    'is_read' => false,
                ]);
            }
        }
        
        $this->command->info('Staff notifications seeded successfully!');
        $this->command->info('Total notifications created: ' . StaffNotification::count());
        $this->command->info('Unread notifications: ' . StaffNotification::where('is_read', false)->count());
    }
}