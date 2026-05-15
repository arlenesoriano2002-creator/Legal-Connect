<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRtcIceCandidate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $recipientId;
    public $candidate;
    public $senderId;

    public function __construct($callId, $recipientId, $candidate, $senderId)
    {
        $this->callId = $callId;
        $this->recipientId = $recipientId;
        $this->candidate = $candidate; // ICE candidate object
        $this->senderId = $senderId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call.user.' . $this->recipientId);
    }

    public function broadcastAs()
    {
        return 'webrtc.ice.candidate';
    }

    public function broadcastWith()
    {
        return [
            'call_id' => $this->callId,
            'candidate' => $this->candidate,
            'sender_id' => $this->senderId,
        ];
    }
}
