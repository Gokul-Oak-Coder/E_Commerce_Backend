<?php

namespace App\Events;

use App\Models\SubOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelledByCustomer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SubOrder $subOrder)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->subOrder->store_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'sub_order_id' => $this->subOrder->id,
            'order_id' => $this->subOrder->order_id,
            'order_number' => $this->subOrder->order->order_number,
            'customer_name' => $this->subOrder->order->customer->name,
            'cancelled_at' => now()->toISOString(),
        ];
    }
}