<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Widgets\AdminDashboardAlerts;
use App\Filament\Widgets\AdminOrderStatusChart;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\AdminSalesTrendChart;
use App\Filament\Widgets\AdminTopProductsChart;
use App\Filament\Widgets\EmployeeOverview;
use App\Filament\Widgets\OrdersTrendChart;
use App\Filament\Widgets\RecentCustomers;
use App\Filament\Widgets\RecentOrders;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\AdminReportService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\InitialAdminSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UsersSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_permissions_restore_navigation_and_authorize_existing_resources(): void
    {
        $admin = $this->userWithRole('admin');
        $this->seed(RoleSeeder::class);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('/admin/products')
            ->assertSee('/admin/categories')
            ->assertSee('/admin/orders')
            ->assertSee('/admin/payments')
            ->assertSee('/admin/users')
            ->assertSee('/admin/discounts')
            ->assertSee('/admin/reports');

        $this->get('/admin/products')->assertOk();
        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/payments')->assertOk();
        $this->get('/admin/shield/roles')->assertOk();
        $this->get('/admin/reports')->assertOk();
        $this->get('/admin/orders/create')->assertNotFound();
        $this->get('/admin/orders/1/edit')->assertNotFound();
        $this->get('/admin/payments/create')->assertNotFound();

        Filament::setCurrentPanel('admin');
        Livewire::actingAs($admin)
            ->test(AdminOverview::class)
            ->assertSee('Collected');
        Livewire::actingAs($admin)
            ->test(AdminSalesTrendChart::class)
            ->assertSee('Collected sales');
        Livewire::actingAs($admin)
            ->test(AdminDashboardAlerts::class)
            ->assertSee('Operational alerts');
        Livewire::actingAs($admin)
            ->test(AdminOrderStatusChart::class)
            ->assertSee('Order status');
        Livewire::actingAs($admin)
            ->test(AdminTopProductsChart::class)
            ->assertSee('Top products');
        Livewire::actingAs($admin)
            ->test(OrdersTrendChart::class)
            ->assertSee('Orders over time');
        Livewire::actingAs($admin)
            ->test(RecentOrders::class)
            ->assertSee('Recent orders');
        Livewire::actingAs($admin)
            ->test(RecentCustomers::class)
            ->assertSee('Latest customers');
        Livewire::actingAs($admin)
            ->test(CreateCategory::class)
            ->fillForm(['name' => 'New category'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['name' => 'New category']);
    }

    public function test_existing_admin_role_permissions_are_seeded_for_policy_protected_resources(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get('/admin/products')->assertForbidden();

        $this->seed(RoleSeeder::class);

        $this->get('/admin/products')->assertOk();
        $this->assertTrue($admin->fresh()->can('ViewAny:Product'));
    }

    public function test_customers_cannot_open_admin_resources_or_reports_directly(): void
    {
        $customer = $this->userWithRole('user');
        $this->seed(RoleSeeder::class);

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/reports')->assertForbidden();
    }

    public function test_empty_dashboard_and_reports_render_useful_empty_states(): void
    {
        $admin = $this->userWithRole('admin');
        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('admin');

        Livewire::actingAs($admin)
            ->test(AdminOverview::class)
            ->assertSee('No settled payments');
        Livewire::actingAs($admin)
            ->test(AdminDashboardAlerts::class)
            ->assertSee('No data quality or coupon expiry alerts.');
        Livewire::actingAs($admin)
            ->test(Reports::class)
            ->assertSee('No settled payments')
            ->assertSee('No settled payments for the selected filters');
    }

    public function test_dashboard_revenue_counts_only_one_completed_payment_per_order_and_separates_currency(): void
    {
        $customer = $this->userWithRole('user');
        $category = Category::query()->create(['name' => 'Meals']);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Lunch bowl',
            'slug' => 'REPORT-TEST-PRODUCT',
            'category_id' => $category->id,
            'price' => 50,
            'review' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $product = Product::query()->findOrFail($productId);

        $usdOrder = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 80,
            'currency' => 'USD',
            'status' => 'completed',
        ]);
        Payment::query()->create([
            'order_id' => (string) $usdOrder->id,
            'status' => 'completed',
            'method' => 'paypal',
            'amount' => 80,
            'currency' => 'USD',
            'paypal_capture_id' => 'CAPTURE-ONE',
        ]);
        Payment::query()->create([
            'order_id' => (string) $usdOrder->id,
            'status' => 'completed',
            'method' => 'paypal',
            'amount' => 80,
            'currency' => 'USD',
            'paypal_capture_id' => 'CAPTURE-TWO',
        ]);
        Payment::query()->create([
            'order_id' => (string) $usdOrder->id,
            'status' => 'paid',
            'method' => 'paypal',
            'amount' => 80,
            'currency' => 'USD',
        ]);

        $egpOrder = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 125,
            'currency' => 'EGP',
            'status' => 'completed',
        ]);
        Payment::query()->create([
            'order_id' => (string) $egpOrder->id,
            'status' => 'completed',
            'method' => 'cash',
            'amount' => 125,
            'currency' => 'EGP',
        ]);

        $pendingOrder = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 900,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
        Payment::query()->create([
            'order_id' => (string) $pendingOrder->id,
            'status' => 'pending',
            'method' => 'paypal',
            'amount' => 900,
            'currency' => 'USD',
        ]);
        Payment::query()->create([
            'order_id' => (string) $pendingOrder->id,
            'status' => 'failed',
            'method' => 'paypal',
            'amount' => 900,
            'currency' => 'USD',
        ]);

        OrderItem::query()->create([
            'order_id' => $usdOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50,
        ]);

        $dashboard = app(AdminReportService::class)->dashboard();
        $report = app(AdminReportService::class)->report(
            now()->startOfDay()->toImmutable(),
            now()->endOfDay()->toImmutable()
        );

        $this->assertSame(2, $dashboard['successful_payment_orders_7d']);
        $this->assertSame(3, $dashboard['payment_orders_attempted_7d']);
        $this->assertSame(66.7, $dashboard['payment_success_rate_7d']);
        $this->assertEquals(80, collect($dashboard['revenue_comparison_7d'])->firstWhere('currency', 'USD')['total']);
        $this->assertEquals(125, collect($dashboard['revenue_comparison_7d'])->firstWhere('currency', 'EGP')['total']);
        $this->assertEquals(80, $report['revenue_by_currency']->firstWhere('currency', 'USD')->total);
        $this->assertSame(2, $report['top_products']->first()->units_sold);
        $this->assertSame(2, $report['category_sales']->first()->units_sold);
        $this->assertSame(3, $report['orders_total']);
        $this->assertSame(1, $report['new_customers']);
        $this->assertSame(1, $report['customers_with_orders']);
        $this->assertSame(1, $report['repeat_customers']);
        $categoryReport = app(AdminReportService::class)->report(
            now()->startOfDay()->toImmutable(),
            now()->endOfDay()->toImmutable(),
            categoryId: $category->id,
        );
        $this->assertSame(1, $categoryReport['orders_total']);
        $this->assertEquals(80, $categoryReport['revenue_by_currency']->firstWhere('currency', 'USD')->total);

        $trend = app(AdminReportService::class)->revenueTrend(
            now()->startOfDay()->toImmutable(),
            now()->endOfDay()->toImmutable(),
            'week'
        );
        $this->assertSame([now()->startOfWeek()->toDateString()], $trend['labels']);
        $this->assertSame(80.0, $trend['datasets'][array_search('USD', array_column($trend['datasets'], 'label'), true)]['data'][0]);
        $ordersTrend = app(AdminReportService::class)->ordersTrend(
            now()->startOfDay()->toImmutable(),
            now()->endOfDay()->toImmutable(),
            'month'
        );
        $this->assertSame(3, $ordersTrend['datasets'][0]['data'][0]);
        $this->assertSame(1, $report['new_customers']);

        $filteredReport = app(AdminReportService::class)->report(
            now()->startOfDay()->toImmutable(),
            now()->endOfDay()->toImmutable(),
            'pending',
            'failed',
            'paypal'
        );
        $this->assertSame(1, $filteredReport['orders_total']);
        $this->assertSame(1, (int) $filteredReport['payment_statuses']->sum('payment_count'));
        $this->assertSame(0.0, $filteredReport['payment_success_rate']);
        $this->assertSame('failed', $filteredReport['payment_statuses']->first()->status);

        $admin = $this->userWithRole('admin');
        $this->seed(RoleSeeder::class);
        $this->assertFalse($admin->can('delete', $category));
        Livewire::actingAs($admin)
            ->test(Reports::class)
            ->set('paymentStatus', 'failed')
            ->set('paymentMethod', 'paypal')
            ->call('applyFilters')
            ->assertSee('Failed')
            ->assertSee('Paypal');
        Livewire::actingAs($admin)
            ->test(Reports::class)
            ->set('fromDate', now()->toDateString())
            ->set('toDate', now()->toDateString())
            ->call('exportOrders')
            ->assertFileDownloaded('foodify-orders-'.now()->toDateString().'-to-'.now()->toDateString().'.csv');
    }

    public function test_employee_has_only_operational_panel_access_and_dashboard_data(): void
    {
        $this->seed(RoleSeeder::class);
        $employee = $this->userWithRole('employee');

        $this->actingAs($employee)
            ->get('/admin')
            ->assertOk()
            ->assertSee('/admin/orders')
            ->assertSee('/admin/products')
            ->assertDontSee('/admin/reports')
            ->assertDontSee('/admin/payments')
            ->assertDontSee('/admin/users')
            ->assertDontSee('/admin/discounts');

        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/payments')->assertForbidden();
        $this->get('/admin/discounts')->assertForbidden();
        $this->get('/admin/reports')->assertForbidden();
        $this->get('/admin/shield/roles')->assertForbidden();
        $this->get('/admin/products/create')->assertForbidden();

        Filament::setCurrentPanel('admin');
        Livewire::actingAs($employee)
            ->test(EmployeeOverview::class)
            ->assertSee('Operations')
            ->assertDontSee('Revenue');
        $this->assertFalse(AdminOverview::canView());
        $this->assertFalse(AdminSalesTrendChart::canView());
        $this->assertFalse(RecentCustomers::canView());
    }

    public function test_customer_activity_report_excludes_staff_orders(): void
    {
        $customer = User::factory()->create();
        $employee = $this->userWithRole('employee');

        foreach ([$customer, $customer, $employee, $employee] as $user) {
            Order::query()->create([
                'user_id' => $user->id,
                'amount' => 100,
                'currency' => 'EGP',
                'status' => 'completed',
            ]);
        }

        $report = app(AdminReportService::class)->report(
            now()->startOfDay()->toImmutable(),
            now()->endOfDay()->toImmutable(),
        );

        $this->assertSame(1, $report['customers_with_orders']);
        $this->assertSame(1, $report['repeat_customers']);
        $this->assertSame($customer->id, $report['top_customers_by_orders']->first()->id);
    }

    public function test_permission_and_role_seeders_are_idempotent_and_keep_unmanaged_permissions(): void
    {
        $this->seed(RoleSeeder::class);
        $customPermission = Permission::findOrCreate('Custom:Retained', 'web');
        $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();
        $employeeRole->givePermissionTo($customPermission);
        $initialRoleCount = Role::query()->count();
        $initialPermissionCount = Permission::query()->count();

        $this->seed(RoleSeeder::class);

        $this->assertSame($initialRoleCount, Role::query()->count());
        $this->assertSame($initialPermissionCount, Permission::query()->count());
        $this->assertTrue($employeeRole->fresh()->hasPermissionTo('ViewAny:Order'));
        $this->assertTrue($employeeRole->fresh()->hasPermissionTo('Custom:Retained'));
        $this->assertFalse($employeeRole->fresh()->hasPermissionTo('View:AdminReports'));

        $admin = Role::query()->where('name', 'admin')->firstOrFail();
        $this->assertTrue($admin->hasPermissionTo('View:AdminReports'));
        $this->assertTrue($admin->hasPermissionTo('View:AdminDashboard'));
        $this->assertTrue($admin->hasPermissionTo('Delete:Category'));
        $this->assertTrue(config('filament-shield.shield_resource.tabs.custom_permissions'));
        $this->assertContains('View:AdminDashboard', config('filament-shield.custom_permissions'));
        $this->assertContains('View:OperationalDashboard', config('filament-shield.custom_permissions'));
    }

    public function test_demo_user_seeder_does_not_modify_an_existing_matching_user(): void
    {
        config([
            'demo.enabled' => true,
            'demo.users.admin' => [
                'name' => 'Configured Admin',
                'email' => 'existing-demo-admin@example.test',
                'phone' => '5551000001',
                'password' => 'new-secret-password',
            ],
            'demo.users.employee' => [
                'name' => 'Configured Employee',
                'email' => 'new-demo-employee@example.test',
                'phone' => '5551000002',
                'password' => 'employee-secret-password',
            ],
        ]);
        $this->seed(RoleSeeder::class);

        $existing = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing-demo-admin@example.test',
            'phone' => '5551000001',
            'password' => 'existing-password',
        ]);
        $existing->assignRole('user');

        $this->seed(UsersSeeder::class);
        $this->seed(UsersSeeder::class);

        $this->assertSame('Existing User', $existing->fresh()->name);
        $this->assertTrue(Hash::check('existing-password', $existing->fresh()->password));
        $this->assertTrue($existing->fresh()->hasRole('user'));
        $this->assertFalse($existing->fresh()->hasRole('admin'));
        $this->assertSame(1, User::query()->where('email', 'new-demo-employee@example.test')->count());
        $this->assertTrue(User::query()->where('email', 'new-demo-employee@example.test')->firstOrFail()->hasRole('employee'));
    }

    public function test_employee_order_views_exclude_financial_and_private_customer_fields(): void
    {
        $this->seed(RoleSeeder::class);
        $employee = $this->userWithRole('employee');
        $customer = User::factory()->create([
            'name' => 'Private Customer',
            'email' => 'private-customer@example.test',
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 333.25,
            'currency' => 'EGP',
            'status' => 'pending',
            'paypal_order_id' => 'PRIVATE-PAYPAL-ORDER',
        ]);

        $this->actingAs($employee)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee('Private Customer')
            ->assertDontSee('333.25')
            ->assertDontSee('PRIVATE-PAYPAL-ORDER');
        $this->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Private Customer')
            ->assertDontSee('private-customer@example.test')
            ->assertDontSee('333.25')
            ->assertDontSee('PRIVATE-PAYPAL-ORDER');
    }

    public function test_initial_admin_bootstrap_is_explicit_repeatable_and_never_promotes_existing_users(): void
    {
        $this->seed(RoleSeeder::class);
        $otherUser = User::factory()->create([
            'email' => 'existing-ordinary-user@example.test',
            'phone' => '5551000004',
        ]);
        $otherUser->assignRole('user');
        config([
            'initial-admin' => [
                'name' => 'First Administrator',
                'email' => 'existing-ordinary-user@example.test',
                'phone' => '5551000003',
                'password' => 'configured-secret',
            ],
        ]);

        try {
            $this->seed(InitialAdminSeeder::class);
            $this->fail('The seeder should reject an existing non-admin account.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('already belongs to a user', $exception->getMessage());
        }

        $this->assertTrue($otherUser->fresh()->hasRole('user'));
        $this->assertFalse($otherUser->fresh()->hasRole('admin'));

        config(['initial-admin.email' => 'first-admin@example.test']);
        $this->seed(InitialAdminSeeder::class);
        $admin = User::query()->where('email', 'first-admin@example.test')->firstOrFail();
        $this->seed(InitialAdminSeeder::class);
        $this->assertSame(1, User::query()->where('email', 'first-admin@example.test')->count());
        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue(Hash::check('configured-secret', $admin->fresh()->password));
    }

    public function test_normal_database_seeder_is_safe_and_does_not_seed_demo_data_by_default(): void
    {
        config(['demo.enabled' => false]);

        $this->seed(DatabaseSeeder::class);
        $roleCount = Role::query()->count();
        $permissionCount = Permission::query()->count();
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($roleCount, Role::query()->count());
        $this->assertSame($permissionCount, Permission::query()->count());
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('products', 0);
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
}
