<?php
namespace App\Services;
use App\Models\Category;
use App\Models\Product;
 

// get product for Home Page
class HomeService
{
 
    
    public function homePage()
    {
         

                return [
                    'latest' => $this->latestProducts(6),
                    'best_selling' => $this->bestSellingProducts(6),
                    'sectionsWithProducts' => $this->sectionsWithProducts(),
                ];
           
    }

private function latestProducts(int $limit)
{
    return Product::with('category')
        ->withExists([
            'favoritedBy as is_favorite' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])
        ->latest()
        ->limit($limit)
        ->get();
}
private function bestSellingProducts(int $limit)
{
    return Product::with('category')
        // ->withCount('order')
        ->withExists([
            'favoritedBy as is_favorite' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])
        ->limit($limit)
        ->get();
}
 private function sectionsWithProducts()
{
    return Category::select('id','name','image') // خدنا id عشان نعمل link
        ->get();
}

 

 
}