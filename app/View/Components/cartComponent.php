<?php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CartComponent extends Component
{
 /** @var array<string, mixed> */
    public array $item ;

    /**
     *@param array<string, mixed> $item
     */
    public function __construct(array $item)
    {
        $this->item = $item;
    }

    public function render(): View|Closure|string
    {
        return view('components.cart-component');
    }
}