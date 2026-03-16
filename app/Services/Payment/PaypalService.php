<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalService
{
    protected $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

public function createPayment(int $userId, float $amount, int $productId): array
{
    return DB::transaction(function () use ($userId, $amount, $productId) {

        $order = Order::create([
            'user_id' => $userId,
            'amount' => $amount,
            'product_id' => $productId,
            'currency' => 'USD',
            'status' => 'pending'
        ]);

        $paypalOrder = $this->paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $amount
                    ],
                    "custom_id" => $order->id
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

        $order->update([
            'paypal_order_id' => $paypalOrder['id']
        ]);

        $approvalUrl = collect($paypalOrder['links'] ?? [])
            ->firstWhere('rel', 'approve')['href']
            ?? collect($paypalOrder['links'] ?? [])
            ->firstWhere('rel', 'payer-action')['href']
            ?? null;

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

    public function capturePayment($token)
    {
        $capture = $this->paypal->capturePaymentOrder($token);

        $status = $capture['status'] ?? null;

        if ($status !== 'COMPLETED') {
            throw new \Exception('Payment not completed');
        }

        $amount = data_get($capture, 'purchase_units.0.payments.captures.0.amount.value');
        $currency = data_get($capture, 'purchase_units.0.payments.captures.0.amount.currency_code');

        DB::transaction(function () use ($capture, $amount, $currency) {

            Payment::create([
                'order_id' => $capture['id'],
                'status' => 'completed',
                'amount' => $amount,
                'currency' => $currency,
                "method"=>"paypal"
            ]);
        });

        return $capture;
    }

    public function handleWebhook($data)
    {
        if (($data['event_type'] ?? null) !== 'PAYMENT.CAPTURE.COMPLETED') {
            return;
        }

        $resource = $data['resource'] ?? [];
        $orderId = $resource['custom_id'] ?? null;

        if (!$orderId) {
            return;
        }

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