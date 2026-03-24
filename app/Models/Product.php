<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
      use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'kcal',
        'review',
        'price',
        'ingredients',
        'category_id',
    ];
    protected $casts = [
    'ingredients' => 'array',
];

       public function category()
    {
        return $this->belongsTo(Category::class);
    }

       public function order()
    {
        return $this->hasMany(Order::class);
    }
public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'favorites')
                ->using(Favorite::class)
                ->withTimestamps();
}
public function carts()
{
    return $this->hasMany(Cart::class);
}

protected static function boot()
{
    parent::boot();

    static::creating(function ($product) {

        $last = self::latest('id')->first();

        if (!$last) {
            $number = 1;
        } else {
            $lastNumber = (int) str_replace('PRODUCT-', '', $last->slug);
            $number = $lastNumber + 1;
        }

        $product->slug = 'PRODUCT-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    });
}

 

}
