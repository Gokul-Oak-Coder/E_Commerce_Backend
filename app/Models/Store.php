<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    //
    protected $fillable = ['seller_id', 'name', 'description', 'logo', 'banner', 'is_active', 'phone', 'address'];
    // Store → Seller
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Store → Products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Store → SubOrders
    public function subOrders()
    {
        return $this->hasMany(SubOrder::class);
    }

    // Store → Reviews
    public function reviews()
    {
        return $this->hasMany(StoreReview::class);
    }
}
