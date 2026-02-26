<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

      $this->call([
            RoleSeeder::class,
            UsersSeeder::class,
            CategoriesSeeder::class,
            ProductSeeder::class,
            OrdersSeeder::class,
            PaymentsSeeder::class,
        ]);
    }
}
