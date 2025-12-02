<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ArchivedAppointment extends Model
{
     protected $table = 'archived_appointments';

    protected $fillable = [
    'fullname',
    'address',
    'phone',
    'email',
    'consulting',
    'selected_date',
    'selected_time',
    'schedule_date',
    'schedule_time',
    'term_status',
    'appointment_approval',
    'id_front',
    'id_back'
];


    // 🔒 Encrypt automatically when saving
   public function setAttribute($key, $value)
{
    $encryptThese = ['fullname', 'address', 'phone', 'email', 'consulting'];

    if (in_array($key, $encryptThese) && $value !== null) {
        try {
            $value = Crypt::encryptString($value);
        } catch (\Exception $e) {
            // fail silently
        }
    }

    return parent::setAttribute($key, $value);
}


    // 🔓 Decrypt automatically when retrieving
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        $decryptThese = ['fullname', 'address', 'phone', 'email', 'consulting'];
        if (in_array($key, $decryptThese) && $value !== null) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {}
        }
        return $value;
    }
}
