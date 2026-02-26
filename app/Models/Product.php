<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
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

       public function category()
    {
        return $this->belongsTo(Category::class);
    }

       public function order()
    {
        return $this->hasMany(Order::class);
    }
    public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
}

}
