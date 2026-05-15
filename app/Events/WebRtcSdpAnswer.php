<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRtcSdpAnswer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $initiatorsId;
    public $sdp;
    public $receiverId;

    public function __construct($callId, $initiatorsId, $sdp, $receiverId)
    {
        $this->callId = $callId;
        $this->initiatorsId = $initiatorsId;
        $this->sdp = $sdp;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call.user.' . $this->initiatorsId);
    }

    public function broadcastAs()
    {
        return 'webrtc.sdp.answer';
    }

    public function broadcastWith()
    {
        return [
            'call_id' => $this->callId,
            'sdp' => $this->sdp,
            'receiver_id' => $this->receiverId,
        ];
    }
}
