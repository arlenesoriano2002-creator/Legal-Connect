<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chattbl';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'message',
        'sender_role',
    ];
}
