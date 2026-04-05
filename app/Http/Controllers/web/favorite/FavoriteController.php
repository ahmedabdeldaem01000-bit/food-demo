<?php

namespace App\Http\Controllers\web\Favorite;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    protected UserService $favoriteProduct;

    public function __construct(UserService $favoriteProduct)
    {
        $this->favoriteProduct = $favoriteProduct;
    }

    public function showFavorite(): View|RedirectResponse //error 20
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->back()->with('error', 'لازم تسجل دخول');
        }
        $favorites = $user['favorites'];//error 23
        return view('web.favorite.index', compact('favorites'));
    }
    public function toggle(int $productId):RedirectResponse // error 26 | Error 26
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->back()->with('error', 'لازم تسجل دخول');
        }

        $status = $this->favoriteProduct->toggleFavorite($user, $productId);

        return redirect()->back()->with(
            'success',
            $status === 'added'
            ? 'تمت إضافة المنتج للمفضلة ❤️'
            : 'تم حذف المنتج من المفضلة 🗑️'
        );
    }
    public function deleteFavorite():View //Error 43
    {
        return view('web.favorite.index');
    }
}
