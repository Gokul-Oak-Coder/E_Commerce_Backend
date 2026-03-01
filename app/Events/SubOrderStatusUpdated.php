<?php

namespace App\Events;

use App\Models\SubOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubOrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SubOrder $subOrder)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'customer.' . $this->subOrder->order->customer_id
            ),
        ];
        // return [
        //     new \Illuminate\Broadcasting\Channel('test-channel'), // ← public, no auth
        // ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'sub_order_id' => $this->subOrder->id,
            'order_id' => $this->subOrder->order_id,
            'order_number' => $this->subOrder->order->order_number,
            'status' => $this->subOrder->status,
            'updated_at' => $this->subOrder->updated_at->toISOString(),
        ];
    }
}