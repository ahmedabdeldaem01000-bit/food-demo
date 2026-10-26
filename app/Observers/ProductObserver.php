<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Models\Notification as ProductNotification;
use App\Notifications\GlobalNotificationMail;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ProductObserver
{
    public function created(Product $product): void
    {
        User::query()
            ->select(['id', 'email'])
            ->chunkById(1000, function ($users) use ($product): void {
                $now = now();

                // Create a notification for each user.
                $notifications = $users->map(function (User $user) use ($product, $now) {
                    return [
                        'notifiable_id' => $user->getKey(),
                        'notifiable_type' => $user->getMorphClass(),
                        'title' => 'منتج جديد',
                        'body' => $product->name,
                        'image' => $product->image,
                        'description' => $product->description,
                        'product_id' => $product->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                if (! empty($notifications)) {
                    ProductNotification::insert($notifications);
                }

                // Send email notifications to the same users.
                NotificationFacade::send(
                    $users,
                    new GlobalNotificationMail(
                        'منتج جديد',
                        $product->name,
                        $product->image
                    )
                );
            });
    }
}