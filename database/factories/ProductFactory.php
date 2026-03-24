<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
  return [
        'name'        => $this->faker->word(),
        'kcal'        => $this->faker->randomNumber(3),
        'review'      => $this->faker->numberBetween(1, 5),
        'slug'      => $this->faker->paragraph(),
        // نبعتها مصفوفة عشان تتوافق مع الـ Cast والـ Validation بتاعك
        'ingredients' => [
            ['name' => $this->faker->word()],
            ['name' => $this->faker->word()],
        ],
        'description' => $this->faker->sentence(),
        'price'       => $this->faker->randomFloat(2, 10, 500),
    //   'category_id' => fn() => Category::inRandomOrder()->value('id') ?? Category::factory(),
        
        'image'       => 'images/image6.jpg',
    ];
    }
}
