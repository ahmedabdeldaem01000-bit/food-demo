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
        $user = $order->user;
      
            $this->sendFirebaseNotification($user->fcm_token ?? null, 'Order Created', 'Your order has been placed!');
        /** @var \App\Models\User $user */
    $user->notify(new OrderCreated($order));
    }

    /**
     * Handle the Order "updated" event.
     */
     public function updated(Order $order): void
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
      /**
       * Summary of sendFirebaseNotification
       * @param mixed $token
       * @param mixed $title
       * @param mixed $body
       * @return void
       */
      private function sendFirebaseNotification($token, $title, $body):void
    {
        if(!$token) return;

        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        $messaging->send($message);
    }
}
