<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title', 
        'message',
        'appointment_id',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship with appointment
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Scope for unread notifications
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope for recent notifications
    public function scopeRecent($query, $limit = 15)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    // Static method to get unread count
    public static function getUnreadCount()
    {
        return self::where('is_read', false)->count();
    }

    // Static method to mark all as read
    public static function markAllAsRead()
    {
        return self::where('is_read', false)->update(['is_read' => true]);
    }
}