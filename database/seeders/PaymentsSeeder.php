<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        Order::factory()
    ->count(10)
    ->create()
    ->each(function ($order) {
        Payment::factory()
            ->count(rand(1, 3)) // عدد مدفوعات لكل order
            ->create([
                'order_id' => $order->id,
            ]);
    });
 
    }
}
