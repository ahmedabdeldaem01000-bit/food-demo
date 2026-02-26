<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    protected $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'product_id' => 'required|exists:products,id',
        ]);
        $order = Order::create([
            'user_id' => $request->user()->id ?? null,
            'amount' => $request->amount,
            'product_id' => $request->product_id,
            'currency' => 'USD',
        ]);

        $paypalOrder = $this->paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->amount
                    ],
                    "custom_id" => $order->id,  // <-- مهم جداً
                    "paypal_order_id" => $request->id,  // <-- مهم جداً
                ]
            ],
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel')
            ]
        ]);


        Log::info('PayPal createOrder response', ['order' => $order]);

        $approvalUrl = collect($order['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        return response()->json([
            'status' => 'success',
            'approval_url' => $approvalUrl,
            'order_id' => $order['id'] ?? null,
            'raw' => $order,
            'paypalOrder' => $paypalOrder,

        ]);
    }

    public function paymentSuccess(Request $request)
    {
        // token عادة بيكون order ID من PayPal return
        $token = $request->query('token');
        $payerId = $request->query('PayerID');

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing token (order id) in redirect URL.'
            ], 422);
        }

        try {
            $capture = $this->paypal->capturePaymentOrder($token);
            Log::info('PayPal capturePaymentOrder response', ['capture' => $capture]);
        } catch (\Exception $e) {
            Log::error('PayPal capture exception', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Exception during capture: ' . $e->getMessage()
            ], 500);
        }

        // تحقق من وجود status
        $status = $capture['status'] ?? ($capture['result']['status'] ?? null);

        // بعض ردود SDK ممكن تكون بعُنصر 'status' او تكون متغطية داخل 'result'
        if ($status === 'COMPLETED' || strcasecmp($status, 'COMPLETED') === 0) {
            // احصل على بيانات المبلغ بأمان
            $amount = data_get($capture, 'purchase_units.0.payments.captures.0.amount.value', data_get($capture, 'result.purchase_units.0.payments.captures.0.amount.value'));
            $currency = data_get($capture, 'purchase_units.0.payments.captures.0.amount.currency_code', data_get($capture, 'result.purchase_units.0.payments.captures.0.amount.currency_code'));

            // فحص قبل الإدخال
            if (!$amount) {
                Log::warning('PayPal capture completed but amount not found', ['capture' => $capture]);
            }

            DB::table('payments')->insert([
                'order_id' => $capture['id'] ?? ($capture['result']['id'] ?? $token),
                'status' => 'completed',
                'amount' => $amount ?? 0,
                'currency' => $currency ?? 'USD',
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment completed successfully.',
                'data' => $capture
            ]);
        }

        // حالة فشل أو غير مكتمل
        return response()->json([
            'status' => 'error',
            'message' => 'Payment not completed. Status: ' . ($status ?? 'unknown'),
            'data' => $capture
        ], 400);
    }

    public function paymentCancel()
    {
        return response()->json([
            'status' => 'cancelled',
            'message' => 'Payment cancelled by user.'
        ]);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('PayPal webhook received', ['data' => $data]);

        if (!isset($data['event_type'])) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if ($data['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = $data['resource'] ?? [];

            $orderId = $resource['custom_id'] ?? null; // هنا ناخد الـ order_id
            if ($orderId) {
                $order = \App\Models\Order::find($orderId);
                if ($order) {
                    $order->status = 'completed';
                    $order->save();

                    \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'status' => 'completed',
                        'amount' => $resource['amount']['value'] ?? 0,
                        'currency' => $resource['amount']['currency_code'] ?? 'USD',
                    ]);
                }
            }
        }


        return response()->json(['status' => 'ok']);
    }
}
