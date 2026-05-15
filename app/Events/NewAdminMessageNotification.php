<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\AdminMessageNotif;

class NewAdminMessageNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        // Broadcast to the specific admin's private channel
        return new PrivateChannel('admin-message-notifications.' . $this->notification->receiver_id);
    }

    public function broadcastAs()
    {
        return 'new-admin-message-notification';
    }

    public function broadcastWith()
    {
        return [
            'notification' => $this->notification,
            'unread_count' => AdminMessageNotif::where('receiver_id', $this->notification->receiver_id)
                ->where('is_read', false)
                ->count()
        ];
    }
}