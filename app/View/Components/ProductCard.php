<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCard extends Component
{
    /**
     * 
     * @var array<string, mixed>
     */
    public $product;

    /**
     * Summary of __construct
     * @param array<string,mixed> $product
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    public function render()
    {
        return view('components.product-card');
    }
}
