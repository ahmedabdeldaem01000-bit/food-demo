<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions must exist before assigning them to roles.
        $this->call([
            // PermissionsSeeder::class,
            RoleSeeder::class,
            UsersSeeder::class,
        ]);

        // Never seed demo business data in production.
        if (
            app()->environment('production')
            || ! config('demo.enabled', false)
        ) {
            return;
        }

        // Preserve existing business data.
        $hasBusinessData = Category::query()->exists()
            || Product::query()->exists()
            || Order::query()->exists()
            || Payment::query()->exists();

        if ($hasBusinessData) {
            return;
        }

        // 2. Seed demo business data in dependency order.
        $this->call([
            CategoriesSeeder::class,
            ProductSeeder::class,
            OrdersSeeder::class,
            PaymentsSeeder::class,
        ]);
    }
}
