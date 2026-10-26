<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
      /** @use HasFactory<PaymentFactory> */
    use HasFactory;
    protected $fillable = [
        'method',
        'order_id',
        'status',
        'amount',
        'currency',
        'paypal_capture_id',
    ];

       /**
        * Summary of Order
        * @return BelongsTo<Order, $this>
        */
       public function Order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
