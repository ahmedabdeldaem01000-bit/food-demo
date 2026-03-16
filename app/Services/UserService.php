<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class UserService
{

    public function getProfile()
    {
        return Auth::user();
    }

    public function updateProfile(array $data)
    {
        $user = Auth::user();
        
        $user->update($data);
        

        return $user->fresh();
    }

    public function getUserOrders()
    {
        $user = Auth::user();

        return $user->order()
                ->with(['items.product'])
            ->latest()
            ->get();
    }

    public function getFavorites()
    {
        return Auth::user()->favorites()->get();
    }

public function toggleFavorite($productId)
{
    $user = auth()->user();
    
    // التبديل التلقائي: لو موجود يحذفه، لو مش موجود يضيفه
    $result = $user->favorites()->toggle($productId);

    //attached تعني أنه تم إضافة المنتج
    if (count($result['attached']) > 0) {
        return 'added';
    }
    
    // detached تعني أنه تم حذف المنتج
    return 'removed';
}

    public function removeFavorite($productId)
    {
        Auth::user()->favorites()->detach($productId);
    }
}