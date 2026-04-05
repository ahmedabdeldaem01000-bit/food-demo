<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity
 */
class Cart extends Model
{
 

protected $fillable=['user_id','product_id','quantity'];
/**
 * Summary of product
 * @return BelongsTo<Product, $this>
 */
public function product(): BelongsTo
{
    return $this->belongsTo(Product::class);
}

/**
 * Summary of user
 * @return BelongsTo<User, $this>
 */
public function user():BelongsTo
{
    return $this->belongsTo(User::class);
}
}
