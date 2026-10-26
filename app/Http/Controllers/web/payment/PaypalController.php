<?php

namespace App\Http\Controllers\web\Payment;

use App\Http\Controllers\Controller;
use App\Models\Cart;
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

        $cart = Cart::query()
            ->where('user_id', $userId)
            ->get();

        if ($cart->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $paypalData = $this->paypalService->createPayment(
            $userId,
            $cart->map(fn (Cart $item): array => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])->all(),
            session('discount_code')
        );

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
        $order = \App\Models\Order::query()
            ->where('paypal_order_id', $token)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // لو الأوردر متدفع قبل كده
        if ($order->status === 'completed') {
       return redirect()->route('paypal.alreadyDone');// صفحة تخبره الدفع تم بالفعل
        }

        // تنفيذ capture لأول مرة
        $this->paypalService->capturePayment($token, (int) auth()->id());

        // مسح الكارت بعد الدفع
        \App\Models\Cart::where('user_id', auth()->id())->delete();
        $request->session()->forget('discount_code');

      return redirect()->route('home.index');

    } catch (\Exception $e) {
        Log::error('PayPal capture failed', [
            'token' => $request->query('token'),
            'message' => $e->getMessage()
        ]);
        return view('web.payment.failed');
    }
}
    public function paymentCancel(Request $request): JsonResponse //118
    {
        $token = $request->query('token');

        if (is_string($token) && $token !== '') {
            $this->paypalService->cancelPayment($token, (int) auth()->id());
        }

        return response()->json([
            'status' => 'cancelled'
        ]);
    }
    public function alreadyDone(): View //118
    {
return view('web.payment.success');
    }

    public function webhook(Request $request): JsonResponse
    {
        if (! $this->paypalService->handleWebhook($request)) {
            return response()->json(['status' => 'invalid_signature'], 403);
        }

        return response()->json(['status' => 'ok']);
    }
}
