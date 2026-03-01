<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('customer.{customerId}', function ($user, $customerId) {
    return (int) $user->id === (int) $customerId;
});

Broadcast::channel('store.{storeId}', function ($user, $storeId) {
    return (int) $user->store?->id === (int) $storeId;
});

Broadcast::channel('delivery.{deliveryId}', function ($user, $deliveryId) {
    return (int) $user->id === (int) $deliveryId
        && $user->role === 'delivery';
});