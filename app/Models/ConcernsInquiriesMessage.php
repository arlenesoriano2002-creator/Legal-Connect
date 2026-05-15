<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcernsInquiriesMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'concerns_inquiries_message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope a query to only include read messages.
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope a query to only include pending messages.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead()
    {
        $this->update(['status' => 'read']);
    }

    /**
     * Mark the message as replied.
     */
    public function markAsReplied()
    {
        $this->update(['status' => 'replied']);
    }

    /**
     * Mark the message as pending.
     */
    public function markAsPending()
    {
        $this->update(['status' => 'pending']);
    }
}