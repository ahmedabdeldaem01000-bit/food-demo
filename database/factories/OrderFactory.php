<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'user_id' => User::inRandomOrder()->first()->id,
            'product_id' => Product::inRandomOrder()->first()->id,
            'amount' => $this->faker->numberBetween(1, 5),
            'currency' => 'USD',
            'status' => $this->faker->randomElement(['pending', 'completed', 'canceled']),
            'stripe_session_id' => $this->faker->uuid(),
        ];
       
    }
}
