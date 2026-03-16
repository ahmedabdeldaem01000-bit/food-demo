<?php

namespace App\Http\Controllers\web\favorite;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
      protected $favoriteProduct;

    public function __construct(UserService $favoriteProduct)
    {
        $this->favoriteProduct = $favoriteProduct;
    }

       public function showFavorite()
    {
        $favorites = auth()->user()->favorites;
        return view('web.favorite.index',compact('favorites'));
    }
public function toggle($productId)
{
    $status = $this->favoriteProduct->toggleFavorite($productId);

    if ($status === 'added') {
        return redirect()->back()->with('success', 'تمت إضافة المنتج للمفضلة ❤️');
    }

    return redirect()->back()->with('success', 'تم حذف المنتج من المفضلة 🗑️');
}
       public function deleteFavorite()
    {
        return view('web.favorite.index');
    }
}
