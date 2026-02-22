<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // Prevent multiple stores
        if ($user->store) {
            return response()->json([
                'message' => 'Store already exists'
            ], 400);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        $store = Store::create([
            'seller_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Store created successfully',
            'store' => $store
        ], 201);
    }

    public function show(Request $request)
    {
        return response()->json([
            'store' => $request->user()->store
        ]);
    }

    public function update(Request $request, Store $store)
    {
        if ($store->seller_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png|max:4096'
        ]);

        if ($request->hasFile('logo')) {

            // Delete old logo if exists
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }

            $logoPath = $request->file('logo')->store('stores/logos', 'public');
            $store->logo = $logoPath;
        }

        if ($request->hasFile('banner')) {

            if ($store->banner) {
                Storage::disk('public')->delete($store->banner);
            }

            $bannerPath = $request->file('banner')->store('stores/banners', 'public');
            $store->banner = $bannerPath;
        }

        $store->update($request->only([
            'name',
            'description',
            'phone',
            'address'
        ]));

        $store->save();

        return response()->json([
            'message' => 'Store updated successfully',
            'store' => $store
        ]);
    }
}
