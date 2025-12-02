<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifApprovalAppointment extends Model
{
    use HasFactory;

    protected $table = 'notifapprovalappointment';

    protected $fillable = [
        'fullname',
        'email', 
        'appointment_approval',
        'appointment_date',
        'appointment_time'
    ];
}