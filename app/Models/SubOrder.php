<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    //
    protected $fillable = [
        'order_id',
        'store_id',
        'delivery_id',
        'subtotal',
        'status',
    ];
    // SubOrder → Main Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // SubOrder → Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // SubOrder → Delivery Person
    public function delivery()
    {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    // SubOrder → Items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
