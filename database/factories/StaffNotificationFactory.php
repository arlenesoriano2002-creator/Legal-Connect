<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StaffNotification;
use App\Models\User;
use App\Models\Appointment;

class StaffNotificationFactory extends Factory
{
    protected $model = StaffNotification::class;

    public function definition()
    {
        $types = ['pending_request', 'appointment_update', 'system', 'message', 'task'];
        
        return [
            'staff_id' => User::where('role', 'staff')->inRandomOrder()->first()->id ?? User::factory(),
            'sender_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'appointment_id' => Appointment::inRandomOrder()->first()->id ?? Appointment::factory(),
            'type' => $this->faker->randomElement($types),
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(2),
            'assigned_to' => $this->faker->randomElement(['individual', 'all']),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
        ];
    }

    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => false,
            ];
        });
    }

    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => true,
            ];
        });
    }

    public function pendingRequest()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'pending_request',
                'title' => 'New Appointment Request',
            ];
        });
    }

    public function appointmentUpdate()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'appointment_update',
                'title' => 'Appointment Status Updated',
            ];
        });
    }

    public function forAllStaff()
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned_to' => 'all',
            ];
        });
    }
}