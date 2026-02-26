<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
             'method' => $this->faker->randomElement(['cash', 'card', 'paypal']),
            'status' => $this->faker->randomElement(['paid', 'pending']),
            'currency' => $this->faker->randomElement(['usd', 'Eg']),
        ];
        /**  protected $fillable = [
        'order_id_DB',
        'order_id',
        'status',
        'amount',
        'currency',
    ]; */
    }
}
