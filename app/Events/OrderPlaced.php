<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($order, $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>|Channel
     */
    public function broadcastOn()
    {
        $userId = is_object($this->user) ? $this->user->id : ($this->user['id'] ?? 1);

        return new PrivateChannel('user.'.$userId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => is_object($this->order) ? ($this->order->id ?? 1) : ($this->order['id'] ?? 1),
            'total' => is_object($this->order) ? ($this->order->total ?? 0) : ($this->order['total'] ?? 0),
            'user_name' => is_object($this->user) ? ($this->user->name ?? 'Customer') : ($this->user['name'] ?? 'Customer'),
        ];
    }
}
