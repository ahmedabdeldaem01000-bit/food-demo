<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    // كل OrderItem مرتبط بـ Order واحد
    /**
     * Summary of order
     * @return BelongsTo<Order, $this>
     */
    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // كل OrderItem مرتبط بمنتج واحد
    /**
     * Summary of product
     * @return BelongsTo<Product, $this>
     */
    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}