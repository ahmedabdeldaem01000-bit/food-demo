<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $imageDirectory = storage_path('app/public/images/products');

        if (! File::isDirectory($imageDirectory)) {
            throw new RuntimeException(
                'Product images directory does not exist in storage.'
            );
        }

        $images = collect(File::files($imageDirectory))
            ->filter(fn ($image) => in_array(
                strtolower($image->getExtension()),
                ['jpg', 'jpeg', 'png', 'webp', 'gif']
            ))
            ->map(fn ($image) => 'images/products/'.$image->getFilename())
            ->values();

        if ($images->isEmpty()) {
            throw new RuntimeException('No product images found in storage.');
        }

        if (! Category::exists()) {
            Category::factory(5)->create();
        }

        $categories = Category::pluck('id');

        if ($categories->isEmpty()) {
            throw new RuntimeException('No categories found for products.');
        }

        for ($i = 0; $i < 30; $i++) {
            Product::factory()->create([
                'category_id' => $categories->random(),
                'image' => $images->random(),
            ]);
        }
    }
}
