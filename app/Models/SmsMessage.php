<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'phone_number',
        'message',
        'message_type',
        'status',
        'message_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Format phone number for display
    public function getFormattedPhoneAttribute()
    {
        $phone = $this->phone_number;
        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        }
        return $phone;
    }

    // Format timestamp for display
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }
}
