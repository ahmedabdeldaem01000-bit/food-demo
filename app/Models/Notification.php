<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
 

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

    /**
     * Summary of reads
     * @return HasMany<NotificationRead, $this>
     */
    public function reads():HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }
}
