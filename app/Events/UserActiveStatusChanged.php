<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserActiveStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user->only(['id', 'name', 'email', 'role', 'active_status']);
    }

    public function broadcastOn(): array
    {
        $adminIds = User::whereIn('role', ['admin', 'superadmin'])->pluck('id');

        return $adminIds
            ->map(fn ($adminId) => new PrivateChannel('admin.' . $adminId))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'user.active-status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => $this->user,
            'user_id' => $this->user['id'],
            'active_status' => (int) $this->user['active_status'],
            'status' => (int) $this->user['active_status'] === 1 ? 'online' : 'offline',
        ];
    }

    public function broadcastWhen(): bool
    {
        if (config('broadcasting.default') !== 'pusher') {
            return true;
        }

        $key = (string) config('broadcasting.connections.pusher.key');
        $secret = (string) config('broadcasting.connections.pusher.secret');
        $appId = (string) config('broadcasting.connections.pusher.app_id');

        if ($this->isInvalidPusherValue($key) || $this->isInvalidPusherValue($secret) || $this->isInvalidPusherValue($appId)) {
            Log::warning('Skipping UserActiveStatusChanged broadcast because Pusher credentials are not configured correctly.', [
                'broadcast_driver' => config('broadcasting.default'),
                'user_id' => $this->user['id'] ?? null,
            ]);

            return false;
        }

        return true;
    }

    private function isInvalidPusherValue(string $value): bool
    {
        $normalized = trim(strtolower($value));

        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, [
            'null',
            'your_app_id',
            'your_app_key',
            'your_app_secret',
        ], true);
    }
}
