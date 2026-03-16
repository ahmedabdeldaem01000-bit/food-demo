<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'status',
        'stripe_session_id',
        'product_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    
    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
    
        public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // لو عايز تجيب المنتجات مباشرة من OrderItems
    public function products()
    {
        return $this->hasManyThrough(
            Product::class,      // الجدول النهائي
            OrderItem::class,    // الجدول الوسيط
            'order_id',          // foreign key في OrderItem يشير لـ Order
            'id',                // key في Product
            'id',                // local key في Order
            'product_id'         // local key في OrderItem
        );
    }
}
