<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = ['admin_id', 'client_id', 'last_message_at'];
    
    protected $dates = ['last_message_at'];
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    
    public function participants()
{
    return $this->hasMany(ChatParticipant::class, 'conversation_id');
}

public function messages()
{
    return $this->hasMany(ChatMessage::class, 'conversation_id');
}
    
    public function unreadMessagesCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }
    
    public function markAsRead($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}