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

Product::factory(100)->create()->each(function ($product) use ($images) {

    $product->update([
        'image' => $images->random()
    ]);

});
    }
}
