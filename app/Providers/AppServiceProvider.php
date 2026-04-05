<?php

namespace App\Providers;
use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    // Order::observe(OrderObserver::class);
 Product::observe(ProductObserver::class);
    View::composer('*', function ($view) {

        $cartCount = 0;
        $unreadCount = 0;

        if (auth()->check()) {
            $userId = auth()->id();

            // cart count
            $cartCount = \App\Models\Cart::where('user_id', $userId)->count();

            // unread notifications count
       $unreadCount = \App\Models\Notification::leftJoin('notification_reads', function ($join) use ($userId) {
    $join->on('notifications.id', '=', 'notification_reads.notification_id')
         ->where('notification_reads.user_id', '=', $userId);
})
->whereNull('notification_reads.id')
->count();
        }

        $view->with([
            'cartCount' => $cartCount,
            'unreadCount' => $unreadCount,
        ]);
    });


}
}