<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CordonDateAvailability extends Model
{
    use HasFactory;

    protected $table = 'cordon_date_availabilities';
    
    protected $fillable = [
        'date',
        'month',
        'date_color',
        'date_description',
        'color',
        'description',
        'booked'
    ];

    protected $casts = [
        'date' => 'date',
        'booked' => 'boolean',
    ];

    // Relationship with time slots
    public function timeSlots()
    {
        return $this->hasMany(CordonTimeSlot::class, 'date', 'date');
    }

    // Get color - use color field (Diffun compatibility)
    public function getColorAttribute()
    {
        return $this->attributes['color'] ?? $this->date_color ?? 'gray';
    }

    // Set color - update both fields for consistency
    public function setColorAttribute($value)
    {
        $this->attributes['color'] = $value;
        $this->attributes['date_color'] = $value;
    }

    // Get description - use description field (Diffun compatibility)
    public function getDescriptionAttribute()
    {
        return $this->attributes['description'] ?? $this->date_description ?? '';
    }

    // Set description - update both fields
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value;
        $this->attributes['date_description'] = $value;
    }

    // Check if date has available slots (simplified for Diffun compatibility)
    public function hasAvailableSlots()
    {
        return $this->color === 'green' && !$this->booked;
    }
}