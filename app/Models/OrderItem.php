<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    // كل OrderItem مرتبط بـ Order واحد
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // كل OrderItem مرتبط بمنتج واحد
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}