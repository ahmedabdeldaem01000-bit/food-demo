<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteDiscountRequest;
use App\Models\Product;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

class DiscountQuoteController extends Controller
{
    public function __invoke(
        QuoteDiscountRequest $request,
        DiscountService $discountService
    ): JsonResponse {
        $validated = $request->validated();
        $product = Product::query()->findOrFail($validated['product_id']);
        $subtotal = round(
            (float) $product->price * (int) $validated['quantity'],
            2
        );
        $quote = $discountService->quote(
            $subtotal,
            $validated['discount_code']
        );

        return response()->json([
            'status' => 'success',
            'discount_code' => $quote['discount']->code,
            'subtotal' => $subtotal,
            'discount_amount' => $quote['discount_amount'],
            'total' => $quote['total'],
            'currency' => 'EGP',
        ]);
    }
}
