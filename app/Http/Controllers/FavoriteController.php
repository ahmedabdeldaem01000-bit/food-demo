<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // ✅ عرض قائمة المفضلة
    public function index()
    {
        $user = Auth::user();

        $favorites = $user->favorites()->get();

        return response()->json([
            'status' => 'success',
            'data' => $favorites,
        ]);
    }

    // ✅ إضافة منتج للمفضلة
    public function store($productId)
    {
        $user = Auth::user();

        $product = Product::findOrFail($productId);

        $user->favorites()->syncWithoutDetaching([$product->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to favorites.',
        ]);
    }

    // ✅ إزالة منتج من المفضلة
    public function destroy($productId)
    {
        $user = Auth::user();

        $user->favorites()->detach($productId);

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from favorites.',
        ]);
    }
}
