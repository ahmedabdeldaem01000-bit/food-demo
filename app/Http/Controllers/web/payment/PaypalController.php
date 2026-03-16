<?php

namespace App\Http\Controllers\web\payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    protected $paypalService;

    public function __construct(PaypalService $paypalService)
    {
        $this->paypal=
        $this->paypalService = $paypalService;
    }


 
   public function createPayment()
{
    $cart = \App\Models\Cart::with('product')
        ->where('user_id', auth()->id())
        ->get();

    if ($cart->isEmpty()) {
        return redirect()->back()->with('error', 'Your cart is empty.');
    }

    // حساب المجموع الكلي
    $totalAmount = $cart->sum(function($item){
        return $item->product->price * $item->quantity;
    });

    // إنشاء Order (دفع كامل)
    $order = \App\Models\Order::create([
        'user_id' => auth()->id(),
        'amount' => $totalAmount,
        'currency' => 'USD',
        'status' => 'pending',
        'method' => 'paypal',

    ]);

    // إنشاء OrderItems لكل منتج في الكارت
    foreach ($cart as $item) {
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product->id,
            'quantity' => $item->quantity,
            'price' => $item->product->price,
        ]);
    }

    // إنشاء طلب PayPal
    $paypal = new \Srmklive\PayPal\Services\PayPal;
    $paypal->setApiCredentials(config('paypal'));
    $paypal->getAccessToken();

    $paypalOrder = $paypal->createOrder([
        "intent" => "CAPTURE",
        "purchase_units" => [
            [
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $totalAmount
                ],
                "custom_id" => $order->id
            ],
        ],
        "application_context" => [
            "return_url" => route('paypal.success'),
            "cancel_url" => route('paypal.cancel')
        ],
           

        
    ]);

$approvalUrl = null;
if (!empty($paypalOrder['links'])) {
    $link = collect($paypalOrder['links'])->firstWhere('rel','approve');
    if ($link && isset($link['href'])) {
        $approvalUrl = $link['href'];
    }
}

if (!$approvalUrl) {
    \Log::error('PayPal approval URL not found', ['paypalOrder' => $paypalOrder]);
    return redirect()->back()->with('error', 'Cannot proceed to PayPal.');
}

    // توجيه المستخدم لصفحة PayPal
    return redirect($approvalUrl);
}
// 
    public function paymentSuccess(Request $request)
    {
 try {
        $token = $request->query('token');

        // تنفيذ الدفع
        $capture = $this->paypalService->capturePayment($token);
        // مسح الكارت بعد الدفع
        \App\Models\Cart::where('user_id', auth()->id())->delete();

        return view('web.payment.success', [
            'payment' => $capture
        ]);

    } catch (\Exception $e) {
        Log::error('PayPal capture failed', ['error' => $e->getMessage()]);
        return view('web.payment.failed');
    }
    }

    public function paymentCancel()
    {
        return response()->json([
            'status' => 'cancelled'
        ]);
    }

    public function webhook(Request $request)
    {
        $this->paypalService->handleWebhook($request->all());

        return response()->json(['status' => 'ok']);
    }
}
