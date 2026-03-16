<?php

namespace App\Http\Controllers\web\cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use function PHPUnit\Framework\throwException;

class CartController extends Controller
{

public function index()
{
    $cart = Cart::with('product')
        ->where('user_id', auth()->id())
        ->get();

$total = $cart->sum(fn($item) => $item->product->price * $item->quantity);


    return view('web.cart.index', compact('cart','total'));
}

 public function decrease(Product $product){
    $cart = Cart::where('user_id', auth()->id())
        ->where('product_id', $product->id)
        ->first();

if ($cart) {
    // لو الكمية أكبر من 1، نقص واحد
    if ($cart->quantity > 1) {
        $cart->decrement('quantity');
        return back()->with('success', 'تم تقليل الكمية');
    } 
    
    // لو الكمية 1 أو أقل، ارمي خطأ أو امسح المنتج
    return back()->withErrors(['msg' => 'لا يمكن تقليل الكمية عن 1، يمكنك حذف المنتج بدلاً من ذلك']);

    } else {

        Cart::updateOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }
        return back();
 }
 public function increase(Product $product){
    $cart = Cart::where('user_id', auth()->id())
        ->where('product_id', $product->id)
        ->first();

    if ($cart) {

        $cart->increment('quantity');

    } else {

        Cart::updateOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }
        return back();
 }
 public function add(Product $product)
{
    $cart = Cart::where('user_id', auth()->id())
        ->where('product_id', $product->id)
        ->first();

    if ($cart) {

        $cart->increment('quantity');

    } else {

        Cart::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    return back();
}

public function remove($id)
{
    Cart::where('user_id', auth()->id())
        ->where('product_id', $id)
        ->delete();

    return back();
}
}
