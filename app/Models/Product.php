<?php

namespace App\Models;
 
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property float $price
 * 
 
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
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


       /**
        * Summary of category
        * @return BelongsTo<Category, $this>
        */
       public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

       /**
        * Summary of order
        * @return HasMany<Order, $this>
        */
       public function order()
    {
        return $this->hasMany(Order::class);
    }
    
/**
 * @return BelongsToMany<User, $this, Favorite>
 */
public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'favorites')
                ->using(Favorite::class)
                ->withTimestamps();
}

/**
 * Summary of carts
 * @return HasMany<Cart, $this>
 */
public function carts():HasMany
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

       $product->slug = 'PRODUCT-' . str_pad((string)$number, 4, '0', STR_PAD_LEFT);
    });
}

 

}
