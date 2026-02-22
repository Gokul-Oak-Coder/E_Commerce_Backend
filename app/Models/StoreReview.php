<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storereview extends Model
{
    //
    protected $fillable = [
        'store_id',
        'customer_id',
        'rating',
        'review',
    ];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
