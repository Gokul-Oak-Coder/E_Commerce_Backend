<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\SubOrder;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    // GET /seller/orders
    public function index(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $subOrders = SubOrder::with(['items.product.images', 'order.customer'])
            ->where('store_id', $store->id)
            ->latest()
            ->get()
            ->map(fn($sub) => $this->formatSubOrder($sub));

        return response()->json($subOrders);
    }

    // GET /seller/orders/{subOrder}
    public function show(Request $request, SubOrder $subOrder)
    {
        $store = $request->user()->store;

        if ($subOrder->store_id !== $store->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            $this->formatSubOrder(
                $subOrder->load(['items.product.images', 'order.customer'])
            )
        );
    }

    // PATCH /seller/orders/{subOrder}/status
    public function updateStatus(Request $request, SubOrder $subOrder)
    {
        $store = $request->user()->store;

        if ($subOrder->store_id !== $store->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:confirmed,processing,shipped,out_for_delivery,delivered,cancelled',
        ]);

        $allowed = $this->getAllowedTransitions($subOrder->status);

        if (!in_array($request->status, $allowed)) {
            return response()->json([
                'message' => "Cannot transition from '{$subOrder->status}' to '{$request->status}'",
                'allowed' => $allowed,
            ], 422);
        }

        // Update status
        $subOrder->update(['status' => $request->status]);

        // Reload with relationships
        $subOrder->load(['items.product.images', 'order.customer']);

        // Sync parent order status
        $this->notificationService->syncOrderStatus($subOrder->order);

        // ✅ Broadcast real-time update to customer
        $this->notificationService->notifyOrderStatusUpdate($subOrder);

        return response()->json([
            'message' => 'Status updated successfully',
            'sub_order' => $this->formatSubOrder($subOrder),
        ]);
    }

    private function getAllowedTransitions(string $current): array
    {
        return match ($current) {
            'placed' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped' => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
            default => [],
        };
    }

    private function formatSubOrder(SubOrder $sub): array
    {
        return [
            'id' => $sub->id,
            'order_number' => $sub->order->order_number ?? 'N/A',
            'order_id' => $sub->order_id,
            'status' => $sub->status,
            'subtotal' => $sub->subtotal,
            'customer_name' => $sub->order->customer->name ?? 'Unknown',
            'customer_phone' => $sub->order->customer->phone ?? '',
            'delivery_address' => $sub->order->delivery_address ?? '',
            'payment_method' => $sub->order->payment_method ?? '',
            'created_at' => $sub->created_at,
            'items' => $sub->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->total,
                'image' => $item->product->images->first()?->image_path
                    ? config('app.url') . '/storage/' . $item->product->images->first()->image_path
                    : null,
            ]),
        ];
    }
}