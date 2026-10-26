<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Services\DiscountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function apply(Request $request, DiscountService $discountService): RedirectResponse
    {
        $validated = $request->validate([
            'discount_code' => ['required', 'string', 'max:64'],
        ]);

        $subtotal = Cart::query()
            ->with('product')
            ->where('user_id', $request->user()->getKey())
            ->get()
            ->sum(fn (Cart $cart): float => (float) $cart->product->price * $cart->quantity);

        if ($subtotal <= 0) {
            return back()->withErrors(['discount_code' => 'Add an item to your cart before applying a discount.']);
        }

        $quote = $discountService->quote((float) $subtotal, $validated['discount_code']);
        $request->session()->put('discount_code', $quote['discount']->code);

        return back()->with('success', 'Discount applied. Your savings: ' . number_format($quote['discount_amount'], 2) . ' EGP.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->session()->forget('discount_code');

        return back()->with('success', 'Discount removed from your cart.');
    }
}
