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

class CallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $call;
    public $callId;
    public $initiatorsUser;
    public $receiverId;

    public function __construct(Call $call)
    {
        $this->call = $call;
        $this->callId = $call->id;
        $this->initiatorsUser = $call->initiator;
        $this->receiverId = $call->receiver_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call.user.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'call.initiated';
    }

    public function broadcastWith()
    {
        return [
            'call_id' => $this->callId,
            'initiator_id' => $this->call->initiator_id,
            'initiator_name' => $this->initiatorsUser->name ?? 'Unknown User',
            'call_type' => $this->call->call_type,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
