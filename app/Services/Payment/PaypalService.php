<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalService
{
    protected PayPalClient $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }
    /** @return array{approval_url:?string, paypal_order_id:string, order_id:int} */
    public function createPayment(int $userId, float $amount, int $productId): array
    {
        return DB::transaction(function () use ($userId, $amount, $productId): array {
            $order = Order::create([
                'user_id' => $userId,
                'amount' => $amount,
               
                'currency' => 'USD',
                'status' => 'pending'
            ]);

            $amount = round($amount, 2);
            $paypalOrder = (array) $this->paypal->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $amount
                        ],
                        "custom_id" => (string) $order->id
                    ]
                ],
                "application_context" => [
                    "return_url" => route('paypal.success'),
                    "cancel_url" => route('paypal.cancel')
                ]
            ]);
            // Log::info($paypalOrder);

            if (!isset($paypalOrder['id'])) {
                Log::error('PayPal order creation failed', [
                    'response' => $paypalOrder
                ]);

                throw new \Exception('Failed to create PayPal order');
            }
        if (isset($paypalOrder['id'])) {
    // تحديث مباشر للعمود
    $order->forceFill([
        'paypal_order_id' => (string) $paypalOrder['id']
    ])->save();

    // سجل في اللوج عشان نتأكد إن الخطوة دي تمت
    \Log::info('Order Step 2: PayPal ID saved to DB', [
        'db_id' => $order->id,
        'paypal_id' => $order->paypal_order_id
    ]);
            } else {
                \Log::error('PayPal Response does not contain ID', ['response' => $paypalOrder]);
                throw new \Exception('PayPal ID missing from response');
            }
            

            $order->update([
                'paypal_order_id' => $paypalOrder['id']
            ]);

            $links = $paypalOrder['links'] ?? [];

            $approvalUrl = null;

            foreach ($links as $link) {
                if (in_array($link['rel'] ?? '', ['approve', 'payer-action'])) {
                    $approvalUrl = $link['href'] ?? null;
                    break;
                }
            }


            Log::info('PayPal order created', [
                'order_id' => $order->id,
                'paypal_order_id' => $paypalOrder['id']
            ]);


            return [
                'approval_url' => $approvalUrl,
                'paypal_order_id' => $paypalOrder['id'],
                'order_id' => $order->id
            ];
        });
    }
    /**
     * @return array<string, mixed>
     */
    //     public function capturePayment(string $token):array
//     {
//         $capture =(array) $this->paypal->capturePaymentOrder($token);
//         $status = $capture['status'] ?? null;

    //         if ($status !== 'COMPLETED') {
//             throw new \Exception('Payment not completed');
//         }

    //         $amount = data_get($capture, 'purchase_units.0.payments.captures.0.amount.value');
//         $currency = data_get($capture, 'purchase_units.0.payments.captures.0.amount.currency_code');

    //         DB::transaction(function () use ($capture, $amount, $currency) {
// $order = Order::where('paypal_order_id', $capture['id'])->firstOrFail();

    //             Payment::create([
//                 'order_id' => $order->id,
//                 'status' => 'completed',
//                 'amount' => $amount,
//                 'currency' => $currency,
//                 "method"=>"paypal"
//             ]);
//         });

    //         return $capture;
//     }


    /**
     * @return array<string, mixed>
     */
    public function capturePayment(string $token): array
    {

        Log::info('Attempting capture', ['token_from_url' => $token]);
        $capture = (array) $this->paypal->capturePaymentOrder($token);

        $status = $capture['status'] ?? null;

        if ($status !== 'COMPLETED') {
            Log::error('PayPal payment not completed', ['capture' => $capture]);
            throw new \Exception('Payment not completed');
        }


        $order = Order::where('paypal_order_id', $token)->first();
        if (!$order) {

            Log::error('Order not found for token', [
                'searched_token' => $token,
                'existing_paypal_ids' => Order::where('status', 'pending')->pluck('paypal_order_id')->toArray()
            ]);
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Order with PayPal ID {$token} not found.");
        }
      

        $amount = data_get($capture, 'purchase_units.0.payments.captures.0.amount.value');
        $currency = data_get($capture, 'purchase_units.0.payments.captures.0.amount.currency_code');

        DB::transaction(function () use ($order, $amount, $currency) {
            // تحديث حالة الطلب الأصلي
            $order->update(['status' => 'completed']);

            Payment::create([
                'order_id' => $order->id,
                'status' => 'completed',
                'amount' => $amount,
                'currency' => $currency,
                "method" => "paypal"

            ]);

        });



        return $capture;
    }
    /**
     * @param array<string, mixed> $data
     */
    public function handleWebhook(array $data): void
    {
        if (($data['event_type'] ?? null) !== 'PAYMENT.CAPTURE.COMPLETED') {
            return;
        }

        $resource = $data['resource'] ?? [];
        $orderId = $resource['custom_id'] ?? null;

        if (!$orderId) {
            return;
        }

        /** @var Order|null $order */
        $order = Order::find($orderId);

        if (!$order) {
            return;
        }

        DB::transaction(function () use ($order, $resource) {

            $order->update([
                'status' => 'completed'
            ]);

            Payment::create([
                'order_id' => $order->id,
                'status' => 'completed',
                'amount' => $resource['amount']['value'] ?? 0,
                'currency' => $resource['amount']['currency_code'] ?? 'USD',
            ]);
        });
    }
}