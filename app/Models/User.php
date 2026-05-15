<?php

namespace App\Models;

use App\Events\UserActiveStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected static function booted()
    {
        static::updated(function (User $user) {
            if ($user->wasChanged('active_status')) {
                event(new UserActiveStatusChanged($user->fresh()));
            }
        });
    }

    protected $fillable = [
        'name',
        'address',
        'cp_number',
        'username',
        'email',
        'password',
        'image',
        'active_status',
        'law_office_id',
        'law_office',
        'role',
        'is_verified',
        'email_verified_at',
        'email_otp',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active_status' => 'integer',
        ];
    }

    public function lawOffice()
    {
        return $this->belongsTo(LawOffice::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

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

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function getProfileImageUrl()
    {
        if ($this->image && Storage::exists('public/' . $this->image)) {
            return asset('storage/' . $this->image);
        }

        return asset('images/default-avatar.png');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (Str::contains($this->image, 'staff_images/')) {
            return asset('storage/' . $this->image);
        }

        return Storage::url($this->image);
    }
}
