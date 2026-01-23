<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'cp_number',
        'username',
        'email',
        'password',
        'image',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    // Add chat relationships
    public function conversationsAsClient()
    {
        return $this->hasMany(ChatConversation::class, 'client_id');
    }
    
    public function conversationsAsAdmin()
    {
        return $this->hasMany(ChatConversation::class, 'admin_id');
    }
    
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }
    
    public function chatParticipants()
    {
        return $this->hasMany(ChatParticipant::class, 'user_id');
    }
    
    public function hasRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($this->role, $roles);
    }
}