<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('location-tracking.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    
    if (!$order) return false;

   
    return $user->id === $order->user_id    ;
});
