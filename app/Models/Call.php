<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Call extends Model
{
    use HasFactory;

    protected $table = 'calls';

    protected $fillable = [
        'initiator_id',
        'receiver_id',
        'call_type',
        'status',
        'initiated_at',
        'answered_at',
        'ended_at',
        'duration_seconds',
        'rejection_reason',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the initiator of the call
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * Get the receiver of the call
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Calculate call duration when ending call
     */
    public function calculateDuration()
    {
        if ($this->answered_at && $this->ended_at) {
            $this->duration_seconds = (int) $this->answered_at->diffInSeconds($this->ended_at);
        } elseif ($this->initiated_at && $this->ended_at) {
            $this->duration_seconds = (int) $this->initiated_at->diffInSeconds($this->ended_at);
        }
        return $this;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDuration()
    {
        $seconds = $this->duration_seconds;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($secs > 0 || empty($parts)) $parts[] = "{$secs}s";

        return implode(' ', $parts);
    }

    /**
     * Check if call is active
     */
    public function isActive()
    {
        return in_array($this->status, ['initiated', 'ringing', 'accepted']) && !$this->ended_at;
    }

    /**
     * Mark call as answered
     */
    public function markAsAnswered()
    {
        $this->status = 'accepted';
        $this->answered_at = now();
        $this->save();
        return $this;
    }

    /**
     * Mark call as ended
     */
    public function markAsEnded()
    {
        $this->status = 'completed';
        $this->ended_at = now();
        $this->calculateDuration();
        $this->save();
        return $this;
    }

    /**
     * Mark call as rejected
     */
    public function markAsRejected($reason = null)
    {
        $this->status = 'rejected';
        $this->ended_at = now();
        if ($reason) {
            $this->rejection_reason = $reason;
        }
        $this->save();
        return $this;
    }

    /**
     * Mark call as missed
     */
    public function markAsMissed()
    {
        $this->status = 'missed';
        $this->ended_at = now();
        $this->save();
        return $this;
    }
}
