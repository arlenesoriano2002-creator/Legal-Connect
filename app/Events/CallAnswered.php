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

class CallAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $call;
    public $callId;
    public $initiatorsId;
    public $receiverId;

    public function __construct(Call $call)
    {
        $this->call = $call;
        $this->callId = $call->id;
        $this->initiatorsId = $call->initiator_id;
        $this->receiverId = $call->receiver_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call.user.' . $this->initiatorsId);
    }

    public function broadcastAs()
    {
        return 'call.answered';
    }

    public function broadcastWith()
    {
        return [
            'call_id' => $this->callId,
            'receiver_id' => $this->receiverId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
