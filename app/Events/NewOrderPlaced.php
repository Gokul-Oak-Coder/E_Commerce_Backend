<?php

namespace App\Events;

use App\Models\Order;
use App\Models\SubOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SubOrder $subOrder)
    {
    }

    // ✅ Broadcast to the specific store's channel
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->subOrder->store_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'sub_order_id' => $this->subOrder->id,
            'order_id' => $this->subOrder->order_id,
            'order_number' => $this->subOrder->order->order_number,
            'customer_name' => $this->subOrder->order->customer->name,
            'customer_phone' => $this->subOrder->order->customer->phone,
            'subtotal' => $this->subOrder->subtotal,
            'items_count' => $this->subOrder->items->count(),
            'payment_method' => $this->subOrder->order->payment_method,
            'delivery_address' => $this->subOrder->order->delivery_address,
            'created_at' => $this->subOrder->created_at->toISOString(),
        ];
    }
}