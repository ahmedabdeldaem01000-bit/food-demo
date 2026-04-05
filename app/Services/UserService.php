<?php

namespace App\Services;


use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class UserService
{
    /**
     * Summary of getProfile
     * @return \App\Models\User 
     */
    public function getProfile()
    {
        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        return $user;
    }

    /**
     * Summary of updateProfile
     * @param array<string, mixed> $data
     * @return \App\Models\User|null
     */
    public function updateProfile(array $data)
    {
        /**
         * @var \App\Models\User|null  $user
         */
        $user = Auth::user();

        if ($user) {
            $user->update($data);
            return $user->fresh();
        }
        return null;
    }

    /**
     * Summary of getUserOrders
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order>
     */
    public function getUserOrders()
    {
        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();

        return $user->order()
            ->with(['items'])
            ->latest()
            ->get();
    }

/**
 * Summary of getFavorites
 * @return Collection<int, \App\Models\Product>
 */
public function getFavorites(): Collection // إضافة الـ Return Type هنا
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if (!$user) {
        return new Collection(); // أو collect() لإنشاء مجموعة فارغة
    }

    // هنا بنرجع الـ Collection اللي فيها المنتجات المفضلة
    return $user->favorites()->get();
}

/**
     * Toggle a product in user favorites.
     * * @param mixed $productId
     * @return string
     */
public function toggleFavorite(User $user, int $productId): string
{
    $result = $user->favorites()->toggle($productId);

    if (!empty($result['attached'])) {
        return 'added';
    }

    return 'removed';
}
}