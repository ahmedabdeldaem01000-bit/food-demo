<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
$images = collect(File::files(public_path('images/products')))
            ->map(fn($img) => 'images/products/'.$img->getFilename());

if (Category::count() === 0) {
            Category::factory(5)->create();
        }


        $categories = Category::pluck('id');
        for ($i = 0; $i < 100; $i++) {
            Product::factory()->create([
                'category_id' => $categories->random(), // بيختار قسم عشوائي
                'image'       => $images->random(),     // بيختار صورة عشوائية
            ]);
        }
 
    }
}
