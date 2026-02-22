<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    protected $fillable = [
        'customer_id',
    ];
    // Cart → Customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Cart → Items
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
