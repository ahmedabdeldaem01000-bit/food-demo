<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $imageDirectory = storage_path('app/public/images/categories');

        if (! File::isDirectory($imageDirectory)) {
            throw new RuntimeException(
                'Category images directory does not exist in storage.'
            );
        }

        $images = collect(File::files($imageDirectory))
            ->filter(fn ($image) => in_array(
                strtolower($image->getExtension()),
                ['jpg', 'jpeg', 'png', 'webp', 'gif']
            ))
            ->map(fn ($image) => 'images/categories/'.$image->getFilename())
            ->values();

        if ($images->isEmpty()) {
            throw new RuntimeException(
                'No category images found in storage.'
            );
        }

        if (Category::exists()) {
            $categories = Category::all();

            foreach ($categories as $category) {
                $category->update([
                    'image' => $images->random(),
                ]);
            }

            return;
        }

        Category::factory(5)->create()->each(function ($category) use ($images) {
            $category->update([
                'image' => $images->random(),
            ]);
        });
    }
}
