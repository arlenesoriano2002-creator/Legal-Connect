<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiffunWalkin extends Model
{
    use HasFactory;

    protected $table = 'diffun_walkins';
    
    protected $fillable = [
        'fullname',
        'contact_number',
        'address',
        'purpose',
        'branch',
        'date_time'
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];
}