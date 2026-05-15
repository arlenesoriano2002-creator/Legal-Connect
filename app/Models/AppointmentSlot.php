<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    protected $table = 'appointment_slots';

    protected $fillable = [
        'date',
        'time_range',
        'capacity_remaining',
        'law_office_id',
    ];

    public function lawOffice()
    {
        return $this->belongsTo(LawOffice::class);
    }

    public function scopeForOffice($query, $officeId)
    {
        return $query->where('law_office_id', $officeId);
    }
}
