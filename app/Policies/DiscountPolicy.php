<?php

namespace App\Policies;

use App\Models\Discount;
use App\Models\User;

class DiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Discount $discount): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Discount $discount): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Discount $discount): bool
    {
        return $user->hasRole('admin') && ! $discount->orders()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
