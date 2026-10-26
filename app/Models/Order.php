<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory>*/
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'status',
        'stripe_session_id',
        'paypal_order_id',
        'product_id',
        'discount_id',
        'discount_amount',
    ];

    /**
     * Summary of user
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Summary of product
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Summary of payment
     *
     * @return HasMany<Payment, $this>
     */
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * @return BelongsTo<Discount, $this>
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        /** @var HasMany<OrderItem, $this> */
        return $this->hasMany(OrderItem::class);
    }

    // لو عايز تجيب المنتجات مباشرة من OrderItems
    /**
     * Summary of products
     *
     * @return HasManyThrough<Product, OrderItem, $this>
     */
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
