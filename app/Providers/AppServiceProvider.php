<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Order;
use App\Observers\OrderObserver;
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
         Order::observe(OrderObserver::class);
    View::composer('*', function ($view) {

        $count = 0;

        if(auth()->check()){
            $count = Cart::where('user_id', auth()->id())->count();
        }

        $view->with('cartCount', $count);
    });
        
    }
}
