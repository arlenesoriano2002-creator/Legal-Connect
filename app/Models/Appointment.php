<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
     use HasFactory;
    protected $table = 'appointments';

    protected $fillable = [
        'fullname',
        'address',
        'phone',
        'email',
        'category',
        'case_name',
        'selected_branch',   // ✅ REQUIRED
        'selected_date',
        'selected_time',
        'term_status',
        'id_front',
        'id_back',
        'appointment_approval',
        'processed_by',
        'law_office_id',
    ];
    

    protected static function booted()
    {
        static::created(function ($appointment) {
            // Create notification when a new pending appointment is created
            if (isset($appointment->appointment_approval) && strtolower(trim($appointment->appointment_approval)) === 'pending') {
                // Delegate to the Notifications controller which contains the
                // business rules for when to create staff notifications.
                \App\Http\Controllers\Notifications\AppointmentNotificationController::createForPendingAppointment($appointment);
            }
        });

        static::updated(function ($appointment) {
            // Create notification when appointment status changes to pending
            if (isset($appointment->appointment_approval) && strtolower(trim($appointment->appointment_approval)) === 'pending' && $appointment->isDirty('appointment_approval')) {
                \App\Http\Controllers\Notifications\AppointmentNotificationController::createForPendingAppointment($appointment);
            }
        });
    }

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

    public function lawOffice()
    {
        return $this->belongsTo(LawOffice::class);
    }

    public function scopeForOffice($query, $officeId)
    {
        return $query->where('law_office_id', $officeId);
    }

    public function scopeWithSelectedTime($query)
    {
        return $query->whereNotNull('selected_time')
            ->where('selected_time', '<>', '');
    }

    public function scopeByApproval($query, string $status)
    {
        return $query->whereRaw("LOWER(TRIM(appointment_approval)) = ?", [strtolower(trim($status))]);
    }
}