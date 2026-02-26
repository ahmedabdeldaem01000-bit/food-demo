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
    
}
