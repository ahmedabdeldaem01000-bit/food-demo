<?php

namespace App\Http\Controllers\web\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use function PHPUnit\Framework\throwException;

class CartController extends Controller
{

public function index():View
{
    $userId =Auth::user();
if(!$userId){
abort(403);
}
   $cart = Cart::with('product')
        ->where('user_id', $userId['id'])
        ->get();
 

    $total = $cart->sum(fn($item) => $item['product']['price'] * $item->quantity);

    return view('web.cart.index', compact('cart','total'));
}

public function decrease(Product $product):RedirectResponse
{
   $userId =Auth::user();

   if($userId){
       
    $cart = Cart::query()->where('user_id', $userId['id'])
        ->where('product_id', $product->id)
        ->first();


    if ($cart) {
        if ($cart->quantity > 1) {
            $cart->decrement('quantity');
            return back()->with('success', 'تم تقليل الكمية');
        }

        return back()->withErrors([
            'msg' => 'لا يمكن تقليل الكمية عن 1'
        ]);
    }
       }

    return back();
}
 public function increase(Product $product):RedirectResponse
 {
    $userId =Auth::user();
    if(!$userId){
        abort(403);
    }
    $cart = Cart::query()->where('user_id', $userId['id'])
        ->where('product_id', $product->id)
        ->first();

    if ($cart) {

        $cart->increment('quantity');

    } else {

        Cart::query()->updateOrCreate([
            'user_id' =>  $userId['id'],
            'product_id' => $product['id'],
            'quantity' => 1
        ]);
    }
        return back();
 }
 public function add(Product $product):RedirectResponse
{
        $userId =Auth::user();
        if(!$userId){
            abort(403);
        }
    $cart = Cart::query()->where('user_id', $userId['id'])
        ->where('product_id', $product['id'])
        ->first();

    if ($cart) {

        $cart->increment('quantity');

    } else {

        Cart::query()->create([
            'user_id' =>     $userId['id'],
            'product_id' => $product['id'],
            'quantity' => 1
        ]);
    }

    return back();
}

public function remove(int $id):RedirectResponse
{
     $userId =Auth::user();
        if(!$userId){
            abort(403);}

    Cart::query()->where('user_id', $userId['id'])
        ->where('product_id', $id)
        ->delete();

    return back();
}
}
