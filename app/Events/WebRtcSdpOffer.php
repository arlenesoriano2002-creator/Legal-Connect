<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRtcSdpOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $receiverId;
    public $sdp;
    public $initiatorId;

    public function __construct($callId, $receiverId, $sdp, $initiatorId)
    {
        $this->callId = $callId;
        $this->receiverId = $receiverId;
        $this->sdp = $sdp;
        $this->initiatorId = $initiatorId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call.user.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'webrtc.sdp.offer';
    }

    public function broadcastWith()
    {
        return [
            'call_id' => $this->callId,
            'sdp' => $this->sdp,
            'initiator_id' => $this->initiatorId,
        ];
    }
}
