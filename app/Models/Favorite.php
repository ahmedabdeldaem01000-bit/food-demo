<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Favorite extends Pivot
{
  
    protected $table = 'favorites';
 
    protected $fillable = [
        'user_id',
        'product_id',
    ];

   
    public $timestamps = true;
    
 
}