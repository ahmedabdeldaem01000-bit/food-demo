<?php

namespace App\Http\Controllers\web\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Services\DiscountService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\throwException;

class CartController extends Controller
{

public function index(DiscountService $discountService):View|RedirectResponse
{
    $userId =Auth::user();
if(!$userId){
abort(403);
}
   $cart = Cart::with('product')
        ->where('user_id', $userId['id'])
        ->get();
 

    $total = $cart->sum(fn($item) => $item['product']['price'] * $item->quantity);

    try {
        $quote = $discountService->quote((float) $total, session('discount_code'));
    } catch (ValidationException $exception) {
        session()->forget('discount_code');

        return redirect()->route('get-cart.index')->withErrors($exception->errors());
    }

    $discount = $quote['discount'];
    $discountAmount = $quote['discount_amount'];
    $discountedSubtotal = $quote['total'];
    $discountCode = $discount?->code;

    return view('web.cart.index', compact(
        'cart',
        'total',
        'discount',
        'discountAmount',
        'discountedSubtotal',
        'discountCode'
    ));
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
