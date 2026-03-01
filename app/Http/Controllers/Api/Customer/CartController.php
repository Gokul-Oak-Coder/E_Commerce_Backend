<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Get or create cart for customer
    private function getCart()
    {
        return Cart::firstOrCreate(['customer_id' => auth()->id()]);
    }

    // GET /customer/cart
    public function index()
    {
        $cart = $this->getCart();
        $cart->load('items.product.images');
        return response()->json($cart);
    }

    // POST /customer/cart/add
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();
        $product = Product::findOrFail($request->product_id);

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->update([
                'quantity' => $item->quantity + $request->quantity,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }

        $cart->load('items.product.images');
        return response()->json($cart);
    }

    // PUT /customer/cart/update/{cartItem}
    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($cartItemId);
        $item->update(['quantity' => $request->quantity]);

        $cart->load('items.product.images');
        return response()->json($cart);
    }

    // DELETE /customer/cart/remove/{cartItem}
    public function remove($cartItemId)
    {
        $cart = $this->getCart();
        $cart->items()->findOrFail($cartItemId)->delete();

        $cart->load('items.product.images');
        return response()->json($cart);
    }

    // DELETE /customer/cart/clear
    public function clear()
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}