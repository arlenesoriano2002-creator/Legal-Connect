<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'sender_id', 'message_type', 'message',
        'file_path', 'file_name', 'file_size', 'file_mime', 'read_at'
    ];
    
    protected $dates = ['read_at'];
    
    public function conversation()
{
    return $this->belongsTo(ChatConversation::class, 'conversation_id');
}
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    public function isFile()
    {
        return $this->message_type === 'file';
    }
}