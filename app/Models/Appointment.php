<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';

    protected $fillable = [
        'fullname',
        'address',
        'phone',
        'email',
        'category',
        'case_name',
        'selected_date',
        'selected_time',
        'term_status',
        'id_front',
        'id_back',
        'appointment_approval',
    ];

    /**
     * Accessor for consulting - combines category and case_name
     * This allows us to use $appointment->consulting as if it were a database column
     */
    public function getConsultingAttribute()
    {
        return $this->category . ' - ' . $this->case_name;
    }

    /**
     * Mutator for consulting - splits back into category and case_name
     * This allows us to set consulting and have it automatically split
     */
    public function setConsultingAttribute($value)
    {
        $parts = explode(' - ', $value);
        $this->attributes['category'] = $parts[0] ?? 'General';
        $this->attributes['case_name'] = $parts[1] ?? 'Consultation';
    }
}