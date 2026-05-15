<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawOffice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lawyer',
        'address',
        'law_office',
        'timezone',
        'max_capacity',
    ];

    protected $casts = [
        'max_capacity' => 'array',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function appointmentSlots()
    {
        return $this->hasMany(AppointmentSlot::class);
    }

    public function officeDateAvailabilities()
    {
        return $this->hasMany(OfficeDateAvailability::class);
    }
}
