<?php

namespace App\Http\Controllers\web\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PaypalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaypalController extends Controller
{
    protected PaypalService $paypalService; //12

    public function __construct(PaypalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }


    public function createPayment(): RedirectResponse
    {
        $userId = auth()->id();

        $cart = \App\Models\Cart::with('product')
            ->where('user_id', $userId)
            ->get();

        if ($cart->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $totalAmount = round($cart->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        }), 2);

        // نمرر أول منتج كـ example، لو عندك أكثر من منتج ممكن تعدل حسب الـ OrderItems
        $productId = $cart->first()->product->id ?? 0;
        // استخدام الـ Service لإنشاء الدفع وربط الأوردر

        $paypalData = $this->paypalService->createPayment($userId, $totalAmount, $productId);
        // dd($paypalData);/
        // إنشاء OrderItems بعد إنشاء الأوردر (optional)
        foreach ($cart as $item) {
            \App\Models\OrderItem::create([
                'order_id' => $paypalData['order_id'],
                'product_id' => $item->product->id ?? null,
                'quantity' => $item->quantity,
                'price' => $item->product->price ?? 0,
            ]);
        }

        return redirect($paypalData['approval_url']);
    }
    public function paymentSuccess(Request $request): View|RedirectResponse
{
    try {
        $token = $request->query('token');
        if (!$token) {
            throw new \Exception('PayPal token is missing.');
        }

        // جلب الأوردر من token
        $order = \App\Models\Order::where('paypal_order_id', $token)->firstOrFail();

        // لو الأوردر متدفع قبل كده
        if ($order->status === 'completed') {
       return redirect()->route('paypal.alreadyDone');// صفحة تخبره الدفع تم بالفعل
        }

        // تنفيذ capture لأول مرة
        $capture = $this->paypalService->capturePayment($token);

        // مسح الكارت بعد الدفع
        \App\Models\Cart::where('user_id', auth()->id())->delete();

        return view('web.payment.success', ['payment' => $capture]);

    } catch (\Exception $e) {
        Log::error('PayPal capture failed', [
            'token' => $request->query('token'),
            'message' => $e->getMessage()
        ]);
        return view('web.payment.failed');
    }
}
    public function paymentCancel(): JsonResponse //118
    {
        return response()->json([
            'status' => 'cancelled'
        ]);
    }
    public function alreadyDone(): View //118
    {
return view('web.home');
    }

    public function webhook(Request $request): JsonResponse //125
    {
        $this->paypalService->handleWebhook($request->all());

        return response()->json(['status' => 'ok']);
    }
}
