<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'price',
        'discount_price',
        'stock',
        'sku',
        'is_active'
    ];
    // Product → Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Product → Images
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Product → Cart Items
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Product → Order Items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Product → Reviews
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
    public function inStock()
    {
        return $this->stock > 0;
    }
    public function getFinalPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }
}
