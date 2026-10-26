<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Payment\PaypalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Tests\TestCase;

class DiscountPaypalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_quote_uses_database_product_price_and_validates_coupon(): void
    {
        $user = User::factory()->create();
        $product = $this->product(100);
        $this->discount(['code' => 'SAVE10', 'value' => 10]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/discounts/quote', [
                'product_id' => $product->id,
                'quantity' => 2,
                'discount_code' => 'save10',
            ])
            ->assertOk()
            ->assertExactJson([
                'status' => 'success',
                'discount_code' => 'SAVE10',
                'subtotal' => 200,
                'discount_amount' => 20,
                'total' => 180,
                'currency' => 'EGP',
            ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/discounts/quote', [
                'product_id' => $product->id,
                'discount_code' => 'MISSING',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('discount_code');
    }

    public function test_api_paypal_creation_ignores_client_amount_and_returns_order_identifiers(): void
    {
        $user = User::factory()->create();
        $product = $this->product(125);
        $service = Mockery::mock(PaypalService::class);
        $service->shouldReceive('createPayment')
            ->once()
            ->with(
                $user->id,
                [['product_id' => $product->id, 'quantity' => 2]],
                'SAVE10',
                route('paypal.succes'),
                route('paypal.cance')
            )
            ->andReturn([
                'approval_url' => 'https://paypal.test/approve',
                'paypal_order_id' => 'PAYPAL-ORDER',
                'order_id' => 42,
                'amount' => 225,
                'discount_amount' => 25,
            ]);
        $this->app->instance(PaypalService::class, $service);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/paypal/create-payment', [
                'product_id' => $product->id,
                'quantity' => 2,
                'discount_code' => 'SAVE10',
                'amount' => 0.01,
            ])
            ->assertOk()
            ->assertExactJson([
                'status' => 'success',
                'approval_url' => 'https://paypal.test/approve',
                'paypal_order_id' => 'PAYPAL-ORDER',
                'order_id' => 42,
                'amount' => 225,
                'discount_amount' => 25,
                'currency' => 'USD',
            ]);
    }

    public function test_paypal_total_uses_server_prices_and_cancelled_orders_release_reservations(): void
    {
        $user = User::factory()->create();
        $product = $this->product(100.25);
        $discount = $this->discount([
            'code' => 'ONEUSE',
            'usage_limit' => 1,
            'value' => 20,
        ]);
        $paypal = $this->paypalClient();
        $paypal->shouldReceive('createOrder')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                $this->assertSame(
                    '80.20',
                    $payload['purchase_units'][0]['amount']['value']
                );

                return true;
            }))
            ->andReturn([
                'id' => 'PAYPAL-ONE',
                'links' => [['rel' => 'approve', 'href' => 'https://paypal.test/approve']],
            ]);
        $service = new PaypalService($paypal);

        $result = $service->createPayment(
            $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
            'ONEUSE'
        );

        $this->assertSame(80.2, $result['amount']);
        $this->assertSame(20.05, $result['discount_amount']);
        $this->assertDatabaseHas('orders', [
            'id' => $result['order_id'],
            'amount' => 80.2,
            'discount_id' => $discount->id,
            'discount_amount' => 20.05,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $result['order_id'],
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.25,
        ]);
        $this->assertSame(0, $discount->fresh()->usage_count);

        try {
            app(\App\Services\DiscountService::class)->quote(
                100.25,
                'ONEUSE',
                lockForUpdate: true
            );
            $this->fail('A pending order must reserve the final coupon use.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('discount_code', $exception->errors());
        }

        $service->cancelPayment('PAYPAL-ONE', $user->id);

        $this->assertSame('cancelled', Order::findOrFail($result['order_id'])->status);
        $this->assertSame(0, $discount->fresh()->usage_count);
        $this->assertSame(
            80.2,
            app(\App\Services\DiscountService::class)->quote(100.25, 'ONEUSE')['total']
        );
    }

    public function test_expired_pending_order_releases_its_coupon_reservation(): void
    {
        $user = User::factory()->create();
        $discount = $this->discount([
            'code' => 'EXPIRING',
            'usage_limit' => 1,
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'amount' => 90,
            'currency' => 'USD',
            'status' => 'pending',
            'discount_id' => $discount->id,
            'discount_amount' => 10,
        ]);
        $order->forceFill(['created_at' => now()->subMinutes(31)])->save();

        $quote = app(\App\Services\DiscountService::class)->quote(
            100,
            'EXPIRING',
            lockForUpdate: true
        );

        $this->assertSame(90.0, $quote['total']);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, $discount->fresh()->usage_count);
    }

    public function test_paypal_order_without_coupon_uses_database_subtotal(): void
    {
        $user = User::factory()->create();
        $product = $this->product(57.5);
        $paypal = $this->paypalClient();
        $paypal->shouldReceive('createOrder')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                $this->assertSame(
                    '57.50',
                    $payload['purchase_units'][0]['amount']['value']
                );

                return true;
            }))
            ->andReturn([
                'id' => 'PAYPAL-NO-DISCOUNT',
                'links' => [['rel' => 'approve', 'href' => 'https://paypal.test/approve']],
            ]);

        $result = (new PaypalService($paypal))->createPayment(
            $user->id,
            [['product_id' => $product->id, 'quantity' => 1]]
        );

        $this->assertSame(57.5, $result['amount']);
        $this->assertSame(0.0, $result['discount_amount']);
        $this->assertNull(Order::findOrFail($result['order_id'])->discount_id);
    }

    public function test_capture_and_repeated_verified_webhook_record_payment_and_usage_once(): void
    {
        $user = User::factory()->create();
        $product = $this->product(100);
        $discount = $this->discount([
            'code' => 'ONCE',
            'usage_limit' => 1,
            'value' => 10,
        ]);
        $paypal = $this->paypalClient();
        $paypal->shouldReceive('createOrder')
            ->once()
            ->andReturn([
                'id' => 'PAYPAL-TWO',
                'links' => [['rel' => 'approve', 'href' => 'https://paypal.test/approve']],
            ]);
        $paypal->shouldReceive('capturePaymentOrder')
            ->once()
            ->with('PAYPAL-TWO')
            ->andReturn([
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => '1',
                    'payments' => ['captures' => [[
                        'id' => 'CAPTURE-1',
                        'status' => 'COMPLETED',
                        'amount' => ['value' => '90.00', 'currency_code' => 'USD'],
                    ]]],
                ]],
            ]);
        $paypal->shouldReceive('setWebHookID')
            ->twice()
            ->with('WEBHOOK-ID')
            ->andReturn($paypal);
        $paypal->shouldReceive('verifyIPN')
            ->twice()
            ->andReturn(['verification_status' => 'SUCCESS']);
        $service = new PaypalService($paypal);
        $result = $service->createPayment(
            $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
            'ONCE'
        );

        $service->capturePayment('PAYPAL-TWO', $user->id);
        $service->capturePayment('PAYPAL-TWO', $user->id);

        $webhook = Request::create('/api/paypal/webhook', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-1',
                'amount' => ['value' => '90.00', 'currency_code' => 'USD'],
                'supplementary_data' => [
                    'related_ids' => ['order_id' => 'PAYPAL-TWO'],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
        config(['paypal.webhook_id' => 'WEBHOOK-ID']);

        $this->assertTrue($service->handleWebhook($webhook));
        $this->assertTrue($service->handleWebhook($webhook));

        $this->assertSame('completed', Order::findOrFail($result['order_id'])->status);
        $this->assertSame(1, $discount->fresh()->usage_count);
        $this->assertSame(1, Payment::query()->where('paypal_capture_id', 'CAPTURE-1')->count());
    }

    public function test_unverified_webhook_is_rejected_without_recording_payment(): void
    {
        config(['paypal.webhook_id' => 'WEBHOOK-ID']);
        $paypal = $this->paypalClient();
        $paypal->shouldReceive('setWebHookID')
            ->once()
            ->with('WEBHOOK-ID')
            ->andReturn($paypal);
        $paypal->shouldReceive('verifyIPN')
            ->once()
            ->andReturn(['verification_status' => 'FAILURE']);

        $request = Request::create(
            '/api/paypal/webhook',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"event_type":"PAYMENT.CAPTURE.COMPLETED"}'
        );

        $this->assertFalse((new PaypalService($paypal))->handleWebhook($request));
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_webhook_route_is_public_and_rejects_unverified_requests(): void
    {
        $service = Mockery::mock(PaypalService::class);
        $service->shouldReceive('handleWebhook')
            ->once()
            ->andReturn(false);
        $this->app->instance(PaypalService::class, $service);

        $this->postJson('/api/paypal/webhook', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        ])
            ->assertForbidden()
            ->assertExactJson(['status' => 'invalid_signature']);
    }

    private function paypalClient(): PayPalClient
    {
        $paypal = Mockery::mock(PayPalClient::class);
        $paypal->shouldReceive('setApiCredentials')
            ->once()
            ->with(config('paypal'));
        $paypal->shouldReceive('getAccessToken')->once();

        return $paypal;
    }

    private function product(float $price): Product
    {
        $category = Category::query()->create(['name' => 'Test category']);

        return Product::query()->create([
            'name' => 'Test meal',
            'slug' => 'test-meal-' . uniqid(),
            'price' => $price,
            'category_id' => $category->id,
            'description' => 'Test description',
            'image' => 'images/test-meal.jpg',
            'review' => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function discount(array $attributes = []): Discount
    {
        static $nextCode = 1;

        return Discount::query()->create(array_merge([
            'name' => 'Test offer',
            'code' => 'TEST' . $nextCode++,
            'type' => 'percentage',
            'value' => 10,
            'minimum_order_amount' => 0,
            'is_active' => true,
            'usage_limit' => null,
            'usage_count' => 0,
        ], $attributes));
    }
}
