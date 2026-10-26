<?php

namespace App\Services\Payment;

use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalService
{
    protected PayPalClient $paypal;

    public function __construct(?PayPalClient $paypal = null)
    {
        $this->paypal = $paypal ?? new PayPalClient();
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    /**
     * @return array{
     *     approval_url: string,
     *     paypal_order_id: string,
     *     order_id: int,
     *     amount: float,
     *     discount_amount: float
     * }
     */
    public function createPayment(
        int $userId,
        array $items,
        ?string $discountCode = null,
        ?string $returnUrl = null,
        ?string $cancelUrl = null
    ): array {
        if ($items === []) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $requestedQuantities = [];
        foreach ($items as $item) {
            $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);

            if (! $productId || ! $quantity || $quantity < 1) {
                throw ValidationException::withMessages([
                    'cart' => 'The cart contains an invalid item.',
                ]);
            }

            $requestedQuantities[$productId] = ($requestedQuantities[$productId] ?? 0) + $quantity;
        }

        $products = Product::query()
            ->whereIn('id', array_keys($requestedQuantities))
            ->get()
            ->keyBy('id');

        if ($products->count() !== count($requestedQuantities)) {
            throw ValidationException::withMessages([
                'cart' => 'One or more products are no longer available.',
            ]);
        }

        $subtotal = 0.0;
        foreach ($requestedQuantities as $productId => $quantity) {
            $subtotal += (float) $products->get($productId)->price * $quantity;
        }
        $subtotal = round($subtotal, 2);

        if ($subtotal <= 0) {
            throw ValidationException::withMessages([
                'cart' => 'The cart total must be greater than zero.',
            ]);
        }

        $order = DB::transaction(function () use (
            $userId,
            $subtotal,
            $discountCode,
            $requestedQuantities,
            $products
        ): Order {
            $quote = app(DiscountService::class)->quote(
                $subtotal,
                $discountCode,
                lockForUpdate: true
            );

            $discount = $quote['discount'];

            $order = Order::create([
                'user_id' => $userId,
                'amount' => round($quote['total'], 2),
                'currency' => 'USD',
                'status' => 'pending',
                'discount_id' => $discount?->getKey(),
                'discount_amount' => $quote['discount_amount'],
            ]);

            foreach ($requestedQuantities as $productId => $quantity) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $products->get($productId)->price,
                ]);
            }

            return $order;
        });

        try {
            $paypalOrder = (array) $this->paypal->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format(
                                (float) $order->amount,
                                2,
                                '.',
                                ''
                            ),
                        ],
                        'custom_id' => (string) $order->id,
                    ],
                ],
                'application_context' => [
                        'return_url' => $returnUrl ?? route('paypal.success'),
                        'cancel_url' => $cancelUrl ?? route('paypal.cancel'),
                ],
            ]);
        } catch (\Throwable $exception) {
            $order->update(['status' => 'cancelled']);

            Log::error('PayPal order creation failed', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $paypalOrderId = $paypalOrder['id'] ?? null;

        if (! is_string($paypalOrderId) || $paypalOrderId === '') {
            $order->update(['status' => 'cancelled']);

            Log::error('PayPal order ID missing', [
                'order_id' => $order->id,
                'response' => $paypalOrder,
            ]);

            throw new \RuntimeException(
                'Failed to create PayPal order.'
            );
        }

        $order->update([
            'paypal_order_id' => $paypalOrderId,
        ]);

        $approvalUrl = collect($paypalOrder['links'] ?? [])
            ->first(
                fn (array $link): bool => in_array(
                    $link['rel'] ?? '',
                    ['approve', 'payer-action'],
                    true
                )
            )['href'] ?? null;

        if (! is_string($approvalUrl) || $approvalUrl === '') {
            $order->update(['status' => 'cancelled']);

            throw new \RuntimeException(
                'PayPal did not return an approval URL.'
            );
        }

        return [
            'approval_url' => $approvalUrl,
            'paypal_order_id' => $paypalOrderId,
            'order_id' => $order->id,
            'amount' => (float) $order->amount,
            'discount_amount' => (float) $order->discount_amount,
        ];
    }

    /**
     * Capture a PayPal order and record the payment.
     *
     * @return array<string, mixed>
     */
    public function capturePayment(string $token, ?int $userId = null): array
    {
        $order = Order::query()
            ->where('paypal_order_id', $token)
            ->firstOrFail();

        if ($userId !== null && (int) $order->user_id !== $userId) {
            abort(404);
        }

        if ($order->status === 'completed') {
            return [
                'status' => 'COMPLETED',
                'order_id' => $order->id,
                'message' => 'Payment already completed.',
            ];
        }

        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'payment' => 'This order cannot be paid.',
            ]);
        }

        if ($order->created_at->lt(
            now()->subMinutes(max(1, (int) config('paypal.order_expiration_minutes', 30)))
        )) {
            $this->cancelPayment($token, $userId);

            throw ValidationException::withMessages([
                'payment' => 'This PayPal order has expired. Please start checkout again.',
            ]);
        }

        $capture = (array) $this->paypal->capturePaymentOrder($token);

        if (($capture['status'] ?? null) !== 'COMPLETED') {
            Log::error('PayPal payment not completed', [
                'order_id' => $order->id,
                'paypal_order_id' => $token,
                'response' => $capture,
            ]);

            throw new \RuntimeException('Payment not completed.');
        }

        $captureId = data_get(
            $capture,
            'purchase_units.0.payments.captures.0.id'
        );
        $customId = data_get($capture, 'purchase_units.0.custom_id');

        $amount = data_get(
            $capture,
            'purchase_units.0.payments.captures.0.amount.value'
        );

        $currency = data_get(
            $capture,
            'purchase_units.0.payments.captures.0.amount.currency_code'
        );

        if (
            ! is_string($captureId)
            || $captureId === ''
            || ($customId !== null && (string) $customId !== (string) $order->id)
            || ! is_numeric($amount)
            || round((float) $amount, 2) !== round((float) $order->amount, 2)
            || $currency !== $order->currency
        ) {
            Log::critical('PayPal amount or currency mismatch', [
                'order_id' => $order->id,
                'expected_amount' => $order->amount,
                'received_amount' => $amount,
                'expected_currency' => $order->currency,
                'received_currency' => $currency,
            ]);

            throw new \RuntimeException(
                'PayPal payment amount or currency mismatch.'
            );
        }

        $this->completeOrder(
            $order,
            $token,
            $captureId,
            $amount,
            $currency
        );

        return $capture;
    }

    private function completeOrder(
        Order $order,
        string $paypalOrderId,
        string $captureId,
        string|int|float $amount,
        string $currency
    ): void {
        DB::transaction(function () use (
            $order,
            $paypalOrderId,
            $captureId,
            $amount,
            $currency
        ): void {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === 'completed') {
                return;
            }

            if (
                $lockedOrder->status !== 'pending'
                || $lockedOrder->paypal_order_id !== $paypalOrderId
            ) {
                throw new \RuntimeException(
                    'Order status changed before payment was recorded.'
                );
            }

            $existingPayment = Payment::query()
                ->where('paypal_capture_id', $captureId)
                ->first();

            if ($existingPayment) {
                if ((int) $existingPayment->order_id !== $lockedOrder->id) {
                    Log::critical('PayPal capture ID is already linked to another order', [
                        'order_id' => $lockedOrder->id,
                        'payment_order_id' => $existingPayment->order_id,
                        'paypal_capture_id' => $captureId,
                    ]);

                    throw new \RuntimeException(
                        'PayPal capture is already linked to another order.'
                    );
                }

                $lockedOrder->update(['status' => 'completed']);

                return;
            }

            if ($lockedOrder->discount_id !== null) {
                $discount = Discount::query()
                    ->whereKey($lockedOrder->discount_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $discount->increment('usage_count');
            }

            $lockedOrder->update(['status' => 'completed']);

            Payment::create([
                'order_id' => (string) $lockedOrder->id,
                'status' => 'completed',
                'amount' => $amount,
                'currency' => $currency,
                'method' => 'paypal',
                'paypal_capture_id' => $captureId,
            ]);
        });
    }

    public function cancelPayment(
        string $paypalOrderId,
        ?int $userId = null
    ): void {
        DB::transaction(function () use (
            $paypalOrderId,
            $userId
        ): void {
            $order = Order::query()
                ->where('paypal_order_id', $paypalOrderId)
                ->when(
                    $userId !== null,
                    fn ($query) => $query->where('user_id', $userId)
                )
                ->lockForUpdate()
                ->first();

            if (! $order || $order->status !== 'pending') {
                return;
            }

            $order->update([
                'status' => 'cancelled',
            ]);
        });
    }

    /** Process a verified PayPal webhook payload. */
    public function handleWebhook(Request $request): bool
    {
        $webhookId = config('paypal.webhook_id');

        if (! is_string($webhookId) || $webhookId === '') {
            throw new \RuntimeException('PayPal webhook ID is not configured.');
        }

        $verification = (array) $this->paypal
            ->setWebHookID($webhookId)
            ->verifyIPN($request);

        if (($verification['verification_status'] ?? null) !== 'SUCCESS') {
            Log::warning('PayPal webhook signature verification failed');

            return false;
        }

        $data = $request->all();

        if (($data['event_type'] ?? null) !== 'PAYMENT.CAPTURE.COMPLETED') {
            return true;
        }

        $resource = $data['resource'] ?? [];
        if (($resource['status'] ?? null) !== 'COMPLETED') {
            return true;
        }

        $paypalOrderId = data_get(
            $resource,
            'supplementary_data.related_ids.order_id'
        );
        $captureId = $resource['id'] ?? null;

        if (! is_string($paypalOrderId) || $paypalOrderId === ''
            || ! is_string($captureId) || $captureId === '') {
            Log::warning('PayPal capture webhook is missing payment identifiers');

            return true;
        }

        $amount = data_get($resource, 'amount.value');
        $currency = data_get($resource, 'amount.currency_code');
        $order = Order::query()
            ->where('paypal_order_id', $paypalOrderId)
            ->first();

        if (! $order) {
            Log::warning('PayPal webhook references an unknown order', [
                'paypal_order_id' => $paypalOrderId,
            ]);

            return true;
        }

        if (
            ! is_numeric($amount)
            || round((float) $amount, 2) !== round((float) $order->amount, 2)
            || $currency !== $order->currency
        ) {
            Log::critical('Webhook payment amount mismatch', [
                'order_id' => $order->id,
                'expected_amount' => $order->amount,
                'received_amount' => $amount,
                'expected_currency' => $order->currency,
                'received_currency' => $currency,
            ]);

            return true;
        }

        $this->completeOrder(
            $order,
            $paypalOrderId,
            $captureId,
            $amount,
            $currency
        );

        return true;
    }
}