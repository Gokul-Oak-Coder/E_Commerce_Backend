<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function index()
    {
        $orders = Order::with('subOrders.items.product.images')
            ->where('customer_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            $order->load('subOrders.items.product.images')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'shipping' => 'required|numeric',
            'tax' => 'required|numeric',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'customer_id' => auth()->id(),
                'order_number' => 'ORD' . strtoupper(Str::random(8)),
                'total_amount' => $request->total_amount,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping,
                'tax' => $request->tax,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'order_status' => 'placed',
            ]);

            // Group items by store
            $itemsByStore = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $storeId = $product->store_id;
                $itemsByStore[$storeId][] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];
            }

            // Create sub-order per store
            foreach ($itemsByStore as $storeId => $items) {
                $subtotal = collect($items)->sum(
                    fn($i) => $i['product']->price * $i['quantity']
                );

                $subOrder = $order->subOrders()->create([
                    'store_id' => $storeId,
                    'subtotal' => $subtotal,
                    'status' => 'placed',
                ]);

                foreach ($items as $item) {
                    $subOrder->items()->create([
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'price' => $item['product']->price,
                        'total' => $item['product']->price * $item['quantity'],
                    ]);
                }
            }

            // Clear cart
            $cart = Cart::where('customer_id', auth()->id())->first();
            if ($cart)
                $cart->items()->delete();

            DB::commit();

            // ✅ Notify sellers after successful order
            $this->notificationService->notifyNewOrder(
                $order->load('subOrders.items', 'customer')
            );

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('subOrders.items'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to place order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ New cancel endpoint
    public function cancel(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only placed orders can be cancelled by customer
        if ($order->order_status !== 'placed') {
            return response()->json([
                'message' => 'Only placed orders can be cancelled',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order->update(['order_status' => 'cancelled']);
            $order->subOrders()->update(['status' => 'cancelled']);

            DB::commit();

            // ✅ Notify sellers about cancellation
            $this->notificationService->notifyOrderCancelled(
                $order->load('subOrders', 'customer')
            );

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->fresh()->load('subOrders.items'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
////
/* <?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // GET /customer/orders
    public function index()
    {
        $orders = Order::with('subOrders.items.product.images')
            ->where('customer_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($orders);
    }

    // GET /customer/orders/{order}
    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            $order->load('subOrders.items.product.images')
        );
    }

    // POST /customer/orders
    public function store(Request $request)
    {

        \Log::info($request->all());

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }


        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'shipping' => 'required|numeric',
            'tax' => 'required|numeric',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Create main order
            $order = Order::create([
                'customer_id' => auth()->id(),
                'order_number' => 'ORD' . strtoupper(Str::random(8)),
                'total_amount' => $request->total_amount,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping,
                'tax' => $request->tax,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'order_status' => 'placed',
            ]);

            // Group items by store
            $itemsByStore = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $storeId = $product->store_id;
                $itemsByStore[$storeId][] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];
            }

            // Create sub order per store
            foreach ($itemsByStore as $storeId => $items) {
                $subtotal = collect($items)->sum(
                    fn($i) => $i['product']->price * $i['quantity']
                );

                $subOrder = $order->subOrders()->create([
                    'store_id' => $storeId,
                    'subtotal' => $subtotal,
                    'status' => 'placed',
                ]);

                foreach ($items as $item) {
                    $subOrder->items()->create([
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'price' => $item['product']->price,
                        'total' => $item['product']->price * $item['quantity'],
                    ]);
                }
            }

            // Clear cart after order placed
            $cart = Cart::where('customer_id', auth()->id())->first();
            if ($cart)
                $cart->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('subOrders.items'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Show actual exception in JSON
            return response()->json([
                'message' => 'Failed to place order',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
} */