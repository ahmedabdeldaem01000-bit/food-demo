<?php

namespace Tests\Feature;

use App\Jobs\DispatchDiscountActivationNotifications;
use App\Jobs\SendDiscountActivationEmail;
use App\Mail\DiscountActivationMail;
use App\Models\Discount;
use App\Models\User;
use App\Notifications\GlobalNotificationMail;
use App\Services\DiscountService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscountAndAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_can_sign_in_but_cannot_access_filament(): void
    {
        $user = $this->userWithRole('user');

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home.index'));

        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertForbidden();
        $this->get('/admin/discounts')->assertForbidden();
    }

    public function test_admin_credentials_on_regular_login_redirect_without_authenticating(): void
    {
        $admin = $this->userWithRole('admin');

        $this->post(route('login.submit'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('filament.admin.auth.login'));

        $this->assertGuest();
        $this->get(route('filament.admin.auth.login'))->assertOk();
    }

    public function test_only_admin_users_can_open_the_panel_and_discount_resource(): void
    {
        $admin = $this->userWithRole('admin');
        $panel = Filament::getPanel('admin');

        $this->assertTrue($admin->canAccessPanel($panel));
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/discounts')->assertOk();
    }

    public function test_unauthenticated_and_logged_out_users_cannot_reuse_admin_pages(): void
    {
        $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));

        $user = $this->userWithRole('user');
        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login.show'));

        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_percentage_and_fixed_discounts_are_calculated_without_negative_totals(): void
    {
        $percentage = $this->discount(['type' => 'percentage', 'value' => 15]);
        $fixed = $this->discount(['type' => 'fixed', 'value' => 250, 'code' => 'FIXED250']);
        $service = app(DiscountService::class);

        $percentageQuote = $service->quote(200, $percentage->code);
        $this->assertSame(30.0, $percentageQuote['discount_amount']);
        $this->assertSame(170.0, $percentageQuote['total']);

        $fixedQuote = $service->quote(100, $fixed->code);
        $this->assertSame(100.0, $fixedQuote['discount_amount']);
        $this->assertSame(0.0, $fixedQuote['total']);
    }

    public function test_inactive_expired_future_and_exhausted_discounts_cannot_be_applied(): void
    {
        $service = app(DiscountService::class);
        $cases = [
            $this->discount(['is_active' => false, 'code' => 'INACTIVE']),
            $this->discount(['ends_at' => now()->subDay(), 'code' => 'EXPIRED']),
            $this->discount(['starts_at' => now()->addDay(), 'code' => 'FUTURE']),
            $this->discount(['usage_limit' => 1, 'code' => 'USED']),
        ];
        $cases[3]->increment('usage_count');

        foreach ($cases as $discount) {
            try {
                $service->quote(100, $discount->code);
                $this->fail('Unavailable discount code should not be accepted.');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_account_shows_only_active_available_coupons_and_links_to_cart(): void
    {
        $user = $this->userWithRole('user');
        $available = $this->discount([
            'name' => 'Welcome offer',
            'code' => 'WELCOME10',
            'value' => 10,
            'minimum_order_amount' => 200,
            'ends_at' => now()->addDays(5),
        ]);
        $inactive = $this->discount(['is_active' => false, 'code' => 'DISABLED']);
        $expired = $this->discount(['ends_at' => now()->subDay(), 'code' => 'OLD']);
        $exhausted = $this->discount(['usage_limit' => 1, 'code' => 'SOLDOUT']);
        $exhausted->increment('usage_count');
        $reserved = $this->discount(['usage_limit' => 1, 'code' => 'RESERVED']);
        DB::table('orders')->insert([
            'user_id' => $user->id,
            'amount' => 90,
            'currency' => 'USD',
            'status' => 'pending',
            'discount_id' => $reserved->id,
            'discount_amount' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('profile.profilePage'));

        $response->assertOk()
            ->assertSee('My Coupons')
            ->assertSee('Welcome offer')
            ->assertSee('WELCOME10')
            ->assertSee('200.00 EGP')
            ->assertSee(route('get-cart.index', ['discount_code' => $available->code]), false)
            ->assertDontSee($inactive->code)
            ->assertDontSee($expired->code)
            ->assertDontSee($exhausted->code)
            ->assertDontSee($reserved->code);
    }

    public function test_account_shows_coupon_empty_state_and_requires_authentication(): void
    {
        $this->get(route('profile.profilePage'))
            ->assertRedirect(route('login.show'));

        $user = $this->userWithRole('user');

        $this->actingAs($user)
            ->get(route('profile.profilePage'))
            ->assertOk()
            ->assertSee('No coupons available right now');
    }

    public function test_coupon_use_link_prefills_the_existing_cart_discount_field(): void
    {
        $user = $this->userWithRole('user');
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Meals',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Lunch bowl',
            'slug' => 'COUPON-CART-TEST',
            'price' => 100,
            'category_id' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('carts')->insert([
            'user_id' => $user->id,
            'product_id' => $productId,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('get-cart.index', ['discount_code' => 'WELCOME10']))
            ->assertOk()
            ->assertSee('value="WELCOME10"', false);
    }

    public function test_discount_notification_links_to_customer_coupons_and_cart(): void
    {
        $user = $this->userWithRole('user');
        $discount = $this->discount(['code' => 'WELCOME10']);
        DB::table('notifications')->insert([
            'title' => 'New offer',
            'body' => 'Use code WELCOME10.',
            'image' => '',
            'product_id' => '',
            'notifiable_id' => (string) $user->id,
            'description' => 'A customer offer',
            'notifiable_type' => $user->getMorphClass(),
            'discount_id' => $discount->id,
            'activation_token' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('notification.index'))
            ->assertOk()
            ->assertSee('View my coupons')
            ->assertSee(route('profile.profilePage').'#my-coupons', false)
            ->assertSee('Go to cart');
    }

    public function test_model_rejects_invalid_percentage_values(): void
    {
        $this->expectException(ValidationException::class);

        $this->discount(['value' => 100.01]);
    }

    public function test_model_rejects_invalid_date_ranges(): void
    {
        $this->expectException(ValidationException::class);

        $this->discount([
            'starts_at' => now()->addDay(),
            'ends_at' => now(),
        ]);
    }

    public function test_cart_discount_route_validates_and_stores_a_server_verified_code(): void
    {
        $user = $this->userWithRole('user');
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Meals',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Lunch bowl',
            'slug' => 'PRODUCT-TEST-001',
            'price' => 100,
            'category_id' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('carts')->insert([
            'user_id' => $user->id,
            'product_id' => $productId,
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->discount(['type' => 'percentage', 'value' => 10, 'code' => 'SAVE10']);

        $this->actingAs($user)
            ->post(route('discount.apply'), ['discount_code' => 'save10'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('SAVE10', session('discount_code'));
    }

    public function test_activation_notifications_are_idempotent_and_exclude_staff(): void
    {
        $admin = $this->userWithRole('admin');
        $employee = $this->userWithRole('employee');
        $customer = $this->userWithRole('user');
        Queue::fake();
        $discount = $this->discount([
            'is_active' => true,
            'code' => 'WELCOME',
            'description' => 'A welcome offer',
        ]);

        $details = [
            'name' => $discount->name,
            'code' => $discount->code,
            'description' => $discount->description,
            'type' => $discount->type,
            'value' => (float) $discount->value,
            'starts_at' => null,
            'ends_at' => null,
            'url' => route('category.index'),
        ];

        $job = new DispatchDiscountActivationNotifications(
            $discount->id,
            $discount->activation_token,
            $details
        );
        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'discount_id' => $discount->id,
            'notifiable_id' => (string) $customer->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'discount_id' => $discount->id,
            'notifiable_id' => (string) $admin->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'discount_id' => $discount->id,
            'notifiable_id' => (string) $employee->id,
        ]);

        $notificationId = (int) DB::table('notifications')->value('id');
        Queue::assertPushed(SendDiscountActivationEmail::class, 1);
        $this->assertNull(DB::table('notifications')->where('id', $notificationId)->value('email_sent_at'));
        Mail::fake();
        $email = new SendDiscountActivationEmail($notificationId, $customer->id, [
            'name' => $discount->name,
            'code' => $discount->code,
            'description' => $discount->description,
            'type' => $discount->type,
            'value' => (float) $discount->value,
            'starts_at' => null,
            'ends_at' => null,
            'url' => route('category.index'),
        ]);
        $email->handle();
        $email->handle();

        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('email_sent_at'));
        Mail::assertSent(DiscountActivationMail::class, 1);
    }

    public function test_existing_email_notification_can_be_faked_without_sending_mail(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        Notification::sendNow($user, new GlobalNotificationMail('Test', 'Test message'));

        Notification::assertSentTo($user, GlobalNotificationMail::class, 1);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function discount(array $attributes = []): Discount
    {
        static $nextCode = 1;

        return Discount::query()->create(array_merge([
            'name' => 'Test offer',
            'code' => 'OFFER'.$nextCode++,
            'type' => 'percentage',
            'value' => 10,
            'minimum_order_amount' => 0,
            'is_active' => true,
            'usage_limit' => null,
            'usage_count' => 0,
        ], $attributes));
    }
}
