<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    protected $fillable = [
        'sub_order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'total',
    ];
    // OrderItem → SubOrder
    public function subOrder()
    {
        return $this->belongsTo(SubOrder::class);
    }

    // OrderItem → Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
