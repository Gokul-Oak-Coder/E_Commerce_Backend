<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;

class PublicProductController extends Controller
{
    public function index()
    {
        $products = Product::with('images')
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json($products);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('images'));
    }
}
