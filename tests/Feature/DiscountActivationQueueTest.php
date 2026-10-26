<?php

namespace Tests\Feature;

use App\Jobs\DispatchDiscountActivationNotifications;
use App\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DiscountActivationQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_new_activation_events_dispatch_notification_fanout(): void
    {
        Queue::fake();

        $discount = Discount::query()->create([
            'name' => 'Launch offer',
            'code' => 'LAUNCH',
            'type' => 'percentage',
            'value' => 15,
            'minimum_order_amount' => 0,
            'is_active' => true,
        ]);

        Queue::assertPushed(DispatchDiscountActivationNotifications::class, 1);

        $originalToken = $discount->activation_token;
        $discount->update(['description' => 'Updated offer copy']);
        $this->assertSame($originalToken, $discount->fresh()->activation_token);
        Queue::assertPushed(DispatchDiscountActivationNotifications::class, 1);

        $discount->update(['is_active' => false]);
        Queue::assertPushed(DispatchDiscountActivationNotifications::class, 1);

        $discount->update(['is_active' => true]);
        $this->assertNotSame($originalToken, $discount->fresh()->activation_token);
        Queue::assertPushed(DispatchDiscountActivationNotifications::class, 2);
    }
}
