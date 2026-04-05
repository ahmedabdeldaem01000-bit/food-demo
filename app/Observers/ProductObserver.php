<?php
namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\GlobalNotificationMail;
use Illuminate\Support\Facades\Notification;
class ProductObserver {
   public function created(Product $product):void
    {
        $notification = \App\Models\Notification::create([
            'title' => 'منتج جديد',
            'body' => $product->name,
            'image' => $product->image,
            'description' => $product->description,
            'created_at' => $product->created_at,
            'product_id' => $product->id,
        ]);

        User::chunk(1000, function ($users) use ($notification) {
            Notification::send($users, new GlobalNotificationMail($notification->title, $notification->body, $notification->image   ));
        });
    }
}