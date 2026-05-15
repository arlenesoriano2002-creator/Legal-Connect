<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $call;
    public $callId;
    public $initiatorsId;
    public $receiverId;
    public $duration;

    public function __construct(Call $call)
    {
        $this->call = $call;
        $this->callId = $call->id;
        $this->initiatorsId = $call->initiator_id;
        $this->receiverId = $call->receiver_id;
        $this->duration = $call->duration_seconds;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('call.user.' . $this->initiatorsId),
            new PrivateChannel('call.user.' . $this->receiverId),
        ];
    }

    public function broadcastAs()
    {
        return 'call.ended';
    }

    public function broadcastWith()
    {
        return [
            'call_id' => $this->callId,
            'duration' => $this->duration,
            'status' => $this->call->status,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
