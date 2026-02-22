<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'customer_id',
        'total_amount',
        'payment_status',
        'order_status',
        'razorpay_order_id',
        'razorpay_payment_id',
    ];
    // Order → Customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Order → SubOrders
    public function subOrders()
    {
        return $this->hasMany(SubOrder::class);
    }
}
