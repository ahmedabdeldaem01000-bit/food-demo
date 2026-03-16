<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = collect(File::files(public_path('images/categories')))
            ->map(fn($img) => 'images/categories/'.$img->getFilename());
     Category::factory(5)->create()->each(function ($category) use ($images) {

    $category->update([
        'image' => $images->random()
    ]);

});
    }
}
