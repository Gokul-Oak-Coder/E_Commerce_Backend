<?php
use App\Http\Controllers\Api\Seller\ProductController;
use App\Http\Controllers\Api\Auth\SellerAuthController;
use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\Seller\StoreController;
use App\Http\Controllers\Api\Public\PublicProductController;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;


Route::prefix('seller')->group(function () {
    Route::post('/login', [SellerAuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'role:seller'])->group(function () {
        Route::post('/logout', [SellerAuthController::class, 'logout']);
        Route::post('/store', [StoreController::class, 'store']);
        Route::get('/store', [StoreController::class, 'show']);
        Route::post('/store/{store}', [StoreController::class, 'update']);

        Route::post('products/{product}', [ProductController::class, 'update']);
        Route::apiResource('products', ProductController::class);
        Route::delete('products/images/{image}', [ProductController::class, 'deleteImage']);

        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Welcome to Seller Dashboard']);
        });
    });
});

// Public endpoints — no auth needed
Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/{product}', [PublicProductController::class, 'show']);
Route::get('/test/stores', function () {
    return Store::with('seller')->get();
});

Route::post('/customer/auth/firebase', [CustomerAuthController::class, 'firebaseLogin']);

Route::get('/test/users', function () {
    return User::all();
});

Route::get('/test/stores', function () {
    return Store::with('seller')->get();
});

Route::get('/test/products', function () {
    return Product::with('store')->get();
});


Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-test', function () {
    return response()->json(['message' => 'Welcome Admin']);
});
Route::middleware(['auth:sanctum', 'role:customer'])->get('/customer-test', function () {
    return response()->json(['message' => 'Welcome Customer']);
});
Route::middleware(['auth:sanctum', 'role:seller'])->get('/seller-test', function () {
    return response()->json(['message' => 'Welcome Seller']);
});