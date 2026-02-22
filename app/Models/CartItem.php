<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    //
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];
    // CartItem → Cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // CartItem → Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
