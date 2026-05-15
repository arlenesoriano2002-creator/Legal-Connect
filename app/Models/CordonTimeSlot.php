<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CordonTimeSlot extends Model
{
    use HasFactory;

    protected $table = 'cordon_time_slots';
    
    protected $fillable = [
        'date',
        'time',
        'time_slot',
        'color',
        'description',
        'booked'
    ];

    protected $casts = [
        'date' => 'date',
        'booked' => 'boolean',
    ];

    // Relationship with date availability
    public function dateAvailability()
    {
        return $this->belongsTo(CordonDateAvailability::class, 'date', 'date');
    }

    // Get time slot display (for compatibility)
    public function getTimeRangeAttribute()
    {
        return $this->time;
    }

    // Check if slot is bookable (simplified for Diffun compatibility)
    public function isBookable()
    {
        return $this->color === 'green' && !$this->booked;
    }

    // Generate time slots for a date - DIFFUN COMPATIBLE VERSION
    public static function generateDailySlots($date)
    {
        $slots = [];
        $timeIntervals = [
            1 => '8:00 AM - 9:00 AM',
            2 => '9:00 AM - 10:00 AM',
            3 => '10:00 AM - 11:00 AM',
            4 => '11:00 AM - 12:00 PM',
            5 => '12:00 PM - 1:00 PM',
            6 => '1:00 PM - 2:00 PM',
            7 => '2:00 PM - 3:00 PM',
            8 => '3:00 PM - 4:00 PM',
            9 => '4:00 PM - 5:00 PM'
        ];
        
        foreach ($timeIntervals as $timeSlot => $timeRange) {
            $slots[] = [
                'date' => $date,
                'time' => $timeRange,
                'time_slot' => $timeSlot,
                'color' => null,
                'description' => null,
                'booked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $slots;
    }
}