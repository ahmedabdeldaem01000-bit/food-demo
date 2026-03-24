<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
   use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'type',
        'image',
        'product_id',
        'description',
        'created_at',
        'expires_at',
    ];

    public function reads()
    {
        return $this->hasMany(NotificationRead::class);
    }
}
