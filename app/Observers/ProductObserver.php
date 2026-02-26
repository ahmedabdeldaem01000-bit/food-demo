<?php

namespace App\Observers;

use App\Models\Product;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
  public function created(Product $product)
    {
        // لو عايز تبعت Notification لكل المستخدمين المسجلين
        $usersTokens = $product->users()->pluck('fcm_token')->filter()->toArray();

        $messaging = Firebase::messaging();
        $notification = Notification::create('New Product', 'A new product has been added: ' . $product->name);

        foreach($usersTokens as $token){
            $message = CloudMessage::withTarget('token', $token)->withNotification($notification);
            $messaging->send($message);
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
