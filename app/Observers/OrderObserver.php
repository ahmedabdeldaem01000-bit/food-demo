<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderCreated;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class OrderObserver
{

    
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
            $this->sendFirebaseNotification($order->user->fcm_token ?? null, 'Order Created', 'Your order has been placed!');
            $order->user->notify(new OrderCreated($order));
    }

    /**
     * Handle the Order "updated" event.
     */
     public function updated(Order $order)
    {
        if($order->isDirty('status') && $order->status === 'delivered') {
            $this->sendFirebaseNotification($order->user->fcm_token ?? null, 'Order Delivered', 'Your order has been delivered!');
        }
    }


    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
      private function sendFirebaseNotification($token, $title, $body)
    {
        if(!$token) return;

        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        $messaging->send($message);
    }
}
