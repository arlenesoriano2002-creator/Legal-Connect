<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMessageNotif extends Model
{
    use HasFactory;

    protected $table = 'admin_message_notif';

    protected $fillable = [
        'type',
        'title',
        'message',
        'sender_id',
        'sender_name',
        'sender_email',
        'receiver_id',
        'message_id',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship to sender (User)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relationship to receiver (User)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Relationship to chat message
    public function chatMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    // Scope for unread notifications
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope for specific receiver
    public function scopeForReceiver($query, $receiverId)
    {
        return $query->where('receiver_id', $receiverId);
    }
}