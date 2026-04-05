<?php
namespace App\Services;
use App\Models\Category;
use App\Models\Product;


// get product for Home Page
class HomeService
{

    /**
     * Summary of homePage
     * @return array{best_selling: \Illuminate\Database\Eloquent\Collection<int, Product>, latest: \Illuminate\Database\Eloquent\Collection<int, Product>, sectionsWithProducts: \Illuminate\Database\Eloquent\Collection<int, Category>}
     */
    public function homePage():array
    {
        return [
            'latest' => $this->latestProducts(6),
            'best_selling' => $this->bestSellingProducts(6),
            'sectionsWithProducts' => $this->sectionsWithProducts(),
        ];
    }

    /**
     * Summary of latestProducts
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    private function latestProducts(int $limit): \Illuminate\Database\Eloquent\Collection
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
      /**
     * Summary of latestProducts
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    private function bestSellingProducts(int $limit): \Illuminate\Database\Eloquent\Collection
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

    /**
     * Summary of sectionsWithProducts
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    private function sectionsWithProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::select('id', 'name', 'image') // خدنا id عشان نعمل link
            ->get();
    }




}