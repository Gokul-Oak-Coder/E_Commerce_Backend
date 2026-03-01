<?php

namespace App\Services;

use App\Events\NewOrderPlaced;
use App\Events\OrderCancelledByCustomer;
use App\Events\SubOrderStatusUpdated;
use App\Models\Order;
use App\Models\SubOrder;

class NotificationService
{
    // ✅ Called when seller updates status → notify customer
    public function notifyOrderStatusUpdate(SubOrder $subOrder): void
    {
        broadcast(new SubOrderStatusUpdated($subOrder))->toOthers();
    }

    // ✅ Called when customer places order → notify each store's seller
    public function notifyNewOrder(Order $order): void
    {
        $order->load('subOrders.items', 'customer');

        foreach ($order->subOrders as $subOrder) {
            broadcast(new NewOrderPlaced($subOrder));
        }
    }

    // ✅ Called when customer cancels order → notify each store's seller
    public function notifyOrderCancelled(Order $order): void
    {
        $order->load('subOrders', 'customer');

        foreach ($order->subOrders as $subOrder) {
            broadcast(new OrderCancelledByCustomer($subOrder));
        }
    }

    // Sync parent order status from sub-orders
    public function syncOrderStatus(Order $order): void
    {
        $statuses = $order->subOrders()->pluck('status')->toArray();

        $orderStatus = match (true) {
            $this->allMatch($statuses, 'cancelled') => 'cancelled',
            $this->allMatch($statuses, 'delivered') => 'delivered',
            in_array('out_for_delivery', $statuses) => 'out_for_delivery',
            in_array('shipped', $statuses) => 'shipped',
            in_array('processing', $statuses) => 'processing',
            in_array('confirmed', $statuses) => 'confirmed',
            default => 'placed',
        };

        $order->update(['order_status' => $orderStatus]);
    }

    private function allMatch(array $statuses, string $status): bool
    {
        return count($statuses) > 0
            && count(array_unique($statuses)) === 1
            && $statuses[0] === $status;
    }
}