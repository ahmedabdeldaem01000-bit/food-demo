<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Foodify application setup

The application uses Laravel 11, Filament 5, Spatie Permission, Laravel Reverb, and the database queue. The customer storefront is served from `/home`; the admin panel is at `/admin`.

### Database and queued work

Configure the database, mail transport, and Reverb application credentials in `.env`. Do not commit real credentials. Then run:

```sh
php artisan migrate --force
php artisan optimize:clear
php artisan queue:work --tries=5
```

Run the queue worker under a process manager in production. Queued jobs that exhaust their retries are recorded in `failed_jobs`; inspect them with `php artisan queue:failed`. After fixing the underlying issue, retry a specific job with `php artisan queue:retry <id>` or review all failures before using `php artisan queue:retry all`. Restart long-running workers after deployment or configuration changes with `php artisan queue:restart`, then start the replacement worker.

Discount activation fan-out and per-user email deliveries use the configured queue. They are dispatched after the discount transaction commits. The custom `notifications` table continues to store per-user storefront notifications; Laravel's polymorphic database notification channel is not used with that table.

## Discounts and PayPal payment flow

### 1. Project overview

The coupon flow connects the existing Filament admin panel, the authenticated storefront cart, the `DiscountService`, order/order-item persistence, and the PayPal Orders API:

1. An admin creates and activates a discount from Filament.
2. A customer applies `discount_code` in the storefront cart, or an authenticated Sanctum client requests an API quote.
3. The checkout service reads product prices from the database, revalidates the coupon, calculates the payable amount, and stores an order snapshot.
4. PayPal receives that server-calculated amount. The capture callback or a signature-verified webhook records a successful payment and consumes one coupon use.

The installed project is Laravel **11.57.0** with Filament **5.0** and `srmklive/paypal` **3.x** (the dependency constraint is `^3.0`). It is not Laravel 12.

### 2. Issues identified in the existing flow

These issues were confirmed by inspecting the application code and migrations before integration:

- Discount management and a storefront coupon field existed in the implementation being extended, but there was no JSON endpoint to quote a coupon for API clients.
- The legacy API PayPal creation endpoint accepted a client-provided `amount`, did not pass a coupon code into the payment service, and therefore could not safely use the submitted amount as a trusted total.
- The storefront applied a code into the session, but the payment service needed a single server-side quote path for both storefront and API payment creation.
- `DiscountService` validated code availability, dates, aggregate usage limit, minimum subtotal, type, and value, but successful payment did not increment `usage_count`.
- Pending PayPal orders had no coupon-use reservation policy or expiry.
- The webhook handler trusted the request body without verifying PayPal's webhook signature. Capture and webhook payment persistence did not share a single locked, idempotent completion path.
- The `payments` table had no PayPal capture identifier or unique capture constraint.
- `order_items.price` was an integer even though product prices are decimal values.
- `orders.discount_id` and `orders.discount_amount` were **already included** in the discount migration; they were not absent in the inspected code.
- The storefront presents product prices and a delivery fee in EGP, while the existing PayPal integration submits the numeric amount as USD and excludes that displayed delivery fee. No reliable conversion or delivery-total policy was present, so this work did not invent one.

### 3. Changes implemented

#### Coupon management and notifications

- `app/Filament/Resources/Discounts/DiscountResource.php`, `Pages/CreateDiscount.php`, `Pages/EditDiscount.php`, `Pages/ListDiscounts.php`, `Pages/ViewDiscount.php`, `Schemas/DiscountForm.php`, `Schemas/DiscountInfolist.php`, and `Tables/DiscountsTable.php`: Filament create, view, edit, search, filter, sorting, status, activation/deactivation confirmation, and guarded deletion UI. The form supports percentage/fixed discounts, dates, aggregate usage limit, minimum subtotal, and activation.
- `app/Policies/DiscountPolicy.php`: restricts coupon management to authorized administrators using the application's existing role/panel setup.
- `app/Models/User.php`, `app/Http/Controllers/web/Auth/AuthController.php`, and `app/Providers/Filament/AdminPanelProvider.php`: allow only the existing `admin` and `employee` roles into Filament, and keep admin credentials from authenticating through the storefront customer login.
- `config/filament-shield.php`, `app/Http/Middleware/EnsureRoleIsAdminOrDriver.php`, `app/Policies/OrderPolicy.php`, `ProductPolicy.php`, `RolePolicy.php`, `UserPolicy.php`, and `database/seeders/UsersSeeder.php`: related role/permission and panel setup included in this worktree; they do not add a second authentication or role system.
- `app/Models/Discount.php`: discount attributes, casts, order relation, status, and uppercase/duplicate-code protection.
- `app/Observers/DiscountObserver.php` and `app/Providers/AppServiceProvider.php`: validate discount settings and dispatch activation fan-out only for newly activated discounts.
- `app/Jobs/DispatchDiscountActivationNotifications.php`, `app/Jobs/SendDiscountActivationEmail.php`, `app/Mail/DiscountActivationMail.php`, and `resources/views/emails/discount-activation.blade.php`: idempotent per-user in-app notification creation and queued email delivery for regular users; admins and drivers are excluded.
- `app/Notifications/GlobalNotificationMail.php`, `app/Http/Controllers/web/NotificationController.php`, and `resources/views/web/profile/notification.blade.php`: notification action-link and storefront notification behavior updates related to discount notices.

#### Quote, checkout, and PayPal integration

- `app/Services/DiscountService.php`: shared code normalization, eligibility checks, minimum subtotal, percentage/fixed calculation, pending-order reservation counting, expiry handling, and current coupon listing through `availableCoupons()`.
- `app/Http/Controllers/web/profile/UserController.php` and `resources/views/web/profile/index.blade.php`: customer-account coupon listing at `/profile`, filtered using the shared current eligibility rules.
- `resources/views/web/cart/index.blade.php`: pre-fills the existing discount-code field when a customer follows a coupon's Use coupon link to the cart.
- `app/Observers/DiscountObserver.php`, `resources/views/web/profile/notification.blade.php`, and `resources/views/emails/discount-activation.blade.php`: activation notices link to the customer's coupon section and cart.
- `tests/Feature/DiscountAndAdminAccessTest.php`: account coupon visibility, empty state/access control, cart prefill, and notification-link coverage.
- `app/Http/Requests/QuoteDiscountRequest.php` and `app/Http/Controllers/DiscountQuoteController.php`: authenticated JSON quote endpoint. The quote contract currently accepts one `product_id` and `quantity`; prices are fetched from the database.
- `app/Http/Requests/CreatePaypalPaymentRequest.php` and `app/Http/Controllers/PaypalController.php`: API payment validation and response; client-submitted amount is not used. Existing API callback paths are retained.
- `app/Http/Controllers/web/DiscountController.php`, `app/Http/Controllers/web/cart/CartController.php`, and `app/Http/Controllers/web/payment/PaypalController.php`: session-based storefront coupon application/removal and checkout forwarding of the current cart's product IDs, quantities, and session code.
- `app/Services/Payment/PaypalService.php`: database-based product pricing, shared coupon quote, order and order-item snapshots, PayPal approval creation, ownership checks when an authenticated web customer returns, order expiry/cancellation, capture validation, and shared locked completion logic for capture and webhook events.
- `app/Models/Order.php` and `app/Models/Payment.php`: order/discount/payment relationships and PayPal capture ID assignment.
- `config/paypal.php` and `.env.example`: webhook ID and pending-order expiry configuration, with blank credential placeholders only.
- `resources/views/web/payment/success.blade.php`, `failed.blade.php`, and `cancel.blade.php`: checkout result presentation updates already present in the worktree.
- The route declarations are in `routes/web.php` and `routes/api.php`; their current worktree changes implement the existing coupon/PayPal routes. The customer-account coupon feature reused the existing `/profile` and `/get-cart` routes and did not modify route files.

#### Database migrations

- `database/migrations/2026_10_25_190000_create_discounts_table.php`: creates `discounts`; adds nullable `orders.discount_id` with a restrictive foreign key and `orders.discount_amount`; adds discount activation/email identifiers to the custom notifications table and a composite idempotency unique index.
- `database/migrations/2026_10_25_204500_add_paypal_capture_id_and_order_item_price_precision.php`: changes `order_items.price` to `decimal(10, 2)` and adds nullable unique `payments.paypal_capture_id`.

### 4. Coupon flow and route reference

1. An admin with existing panel access creates a code at `/admin/discounts`, chooses `percentage` or `fixed`, configures its value, optional dates, minimum order, aggregate usage limit, and active state.
2. The customer receives the code outside the checkout flow or from the activation notification, then enters it in the storefront cart. The cart form submits `discount_code` to `POST /discount/apply`. The storefront retains the accepted code in the session; checkout rechecks it.
3. An API client can validate a code using `POST /api/discounts/quote`. The JSON quote requires `product_id`, optional `quantity`, and `discount_code`; it does not accept a client subtotal or discount value.
4. `DiscountService::quote()` normalizes the code and checks existence, active state, start/end dates, aggregate usage limit including live pending-order reservations, minimum subtotal, discount type, and value. Invalid conditions produce a validation error.
5. `PaypalService::createPayment()` retrieves the selected product(s) and current prices from the database, calculates the subtotal and discount, then stores `orders.amount`, `currency`, `discount_id`, `discount_amount`, and `order_items` in a transaction.
6. The stored amount is passed to PayPal as the order's USD value. No amount supplied by the API client is used.
7. A successful verified capture increments `discounts.usage_count` once. A pending order counts as a temporary reservation against `usage_limit`; a cancellation or expiry releases it without incrementing usage.
8. Repeated capture returns the completed status without creating another payment. Verified repeat webhooks are idempotent through order locking and the unique `paypal_capture_id`. A non-completed capture event does not complete an order.

| HTTP method | Endpoint | Purpose | Authentication | Actual method | Actual route name | Middleware |
|---|---|---|---|---|---|---|
| POST | `/discount/apply` | Validate and store storefront coupon in session | Web session required | `App\Http\Controllers\web\DiscountController::apply` | `discount.apply` | `web`, `auth`, CSRF |
| DELETE | `/discount/remove` | Remove storefront coupon from session | Web session required | `App\Http\Controllers\web\DiscountController::remove` | `discount.remove` | `web`, `auth`, CSRF |
| POST | `/paypal/create-payment` | Create payment for the authenticated storefront cart | Web session required | `App\Http\Controllers\web\Payment\PaypalController::createPayment` | `paypal.createPayment` | `web`, `auth`, CSRF |
| GET | `/paypal/success` | Capture PayPal return for storefront checkout | Web session required | `App\Http\Controllers\web\Payment\PaypalController::paymentSuccess` | `paypal.success` | `web`, `auth` |
| GET | `/paypal/cancel` | Cancel the current user's pending storefront order | Web session required | `App\Http\Controllers\web\Payment\PaypalController::paymentCancel` | `paypal.cancel` | `web`, `auth` |
| POST | `/api/discounts/quote` | Return a server-priced coupon quote for one product/quantity | Sanctum | `App\Http\Controllers\DiscountQuoteController::__invoke` | `api.discounts.quote` | `api`, `auth:sanctum` |
| POST | `/api/paypal/create-payment` | Create a PayPal order for one product/quantity | Sanctum | `App\Http\Controllers\PaypalController::createPayment` | Unnamed | `api`, `auth:sanctum` |
| GET | `/api/paypal/succes` | Capture API PayPal return using the query `token` | No application auth middleware | `App\Http\Controllers\PaypalController::paymentSuccess` | `paypal.succes` | `api` |
| GET | `/api/paypal/cance` | Cancel API PayPal order using the query `token` | No application auth middleware | `App\Http\Controllers\PaypalController::paymentCancel` | `paypal.cance` | `api` |
| POST | `/api/paypal/webhook` | Verify and process PayPal webhook events | PayPal signature verification, not user auth | `App\Http\Controllers\PaypalController::webhook` | `api.paypal.webhook` | `api` |
| POST | `/paypal/webhook` | Existing web-stack webhook alias | PayPal signature verification, not user auth | `App\Http\Controllers\web\Payment\PaypalController::webhook` | Unnamed | `web`, CSRF excluded |

The API success/cancel callbacks are public because PayPal returns to them; the success callback still calls PayPal capture and verifies the captured amount and currency against the stored order. The API cancel callback relies on the unguessable PayPal token and does not receive an authenticated user ID. The `/paypal/webhook` web-stack alias remains alongside the canonical `/api/paypal/webhook`; register the canonical API endpoint in PayPal.

### 5. API examples

All examples use fictional IDs and codes. Authenticated API calls require the existing Sanctum authentication mechanism. Do not send client-calculated prices.

#### Coupon quote

Request to `POST /api/discounts/quote`:

```json
{
  "product_id": 12,
  "quantity": 2,
  "discount_code": "SAVE10"
}
```

Successful response (`200`):

```json
{
  "status": "success",
  "discount_code": "SAVE10",
  "subtotal": 200.0,
  "discount_amount": 20.0,
  "total": 180.0,
  "currency": "EGP"
}
```

Invalid, expired, inactive, exhausted, or minimum-order-ineligible code (`422`):

```json
{
  "message": "This discount code is not currently available.",
  "errors": {
    "discount_code": [
      "This discount code is not currently available."
    ]
  }
}
```

The exact `message` varies with the failed validation rule.

#### Create a PayPal order without a coupon

Request to authenticated `POST /api/paypal/create-payment`:

```json
{
  "product_id": 12,
  "quantity": 1
}
```

Example successful response (`200`):

```json
{
  "status": "success",
  "approval_url": "https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-42",
  "paypal_order_id": "PAYPAL-ORDER-42",
  "order_id": 42,
  "amount": 100.0,
  "discount_amount": 0.0,
  "currency": "USD"
}
```

#### Create a PayPal order with a coupon

Request:

```json
{
  "product_id": 12,
  "quantity": 2,
  "discount_code": "SAVE10"
}
```

If the database product price is `100.00`, the server stores a subtotal of `200.00`, a discount of `20.00`, and submits `180.00` as the PayPal amount. The successful response has the same shape as above with the corresponding IDs and values. A client-provided `amount` field is ignored.

#### Successful API payment callback

`GET /api/paypal/succes?token=PAYPAL-ORDER-42` returns the captured PayPal data wrapped by the application:

```json
{
  "status": "success",
  "message": "Payment completed",
  "data": {
    "id": "PAYPAL-ORDER-42",
    "status": "COMPLETED",
    "purchase_units": [
      {
        "payments": {
          "captures": [
            {
              "id": "CAPTURE-77",
              "status": "COMPLETED",
              "amount": {
                "currency_code": "USD",
                "value": "180.00"
              }
            }
          ]
        }
      }
    ]
  }
}
```

The shape of `data` is supplied by PayPal's capture response. If the callback is missing its token, it returns `422`; if capture fails or the order cannot be verified, the API returns a generic `400` payment error. An unverified webhook receives `403` with `{"status":"invalid_signature"}`.

### 6. Database changes and idempotency

- `discounts` stores code, type/value, active window, minimum subtotal, aggregate `usage_limit` and `usage_count`, and activation token.
- `orders.discount_id` is nullable and references `discounts.id`; `orders.discount_amount` stores the applied discount snapshot. `Order::discount()` belongs to a discount; `Discount::orders()` returns its orders.
- `orders.items()` contains purchased products, quantities, and the server-read unit price. `order_items.price` is now `decimal(10,2)`.
- `payments` stores successful payment method/status/amount/currency and the unique nullable `paypal_capture_id`. `Order::payment()` is a has-many relationship; `Payment::Order()` belongs to an order. The existing `payments.order_id` column is a string and has no declared foreign key in the inspected migration; the migration added here does not change that legacy schema.
- The `notifications` composite unique index on `(discount_id, activation_token, notifiable_id)` prevents repeating an in-app activation notice for the same user/activation.
- Order row locks guard completion and usage increments. The unique payment capture ID prevents the same PayPal capture from being recorded twice. The schema does not uniquely constrain `payments.order_id`; application completion guards use the order status and lock.

Migrations:

```sh
php artisan migrate --force
php artisan migrate:status
```

At the time this README was updated, `php artisan migrate:status` reported both `2026_10_25_190000_create_discounts_table` and `2026_10_25_204500_add_paypal_capture_id_and_order_item_price_precision` as **Ran**. `php artisan db:table payments` confirmed the unique `payments_paypal_capture_id_unique` index, and `php artisan db:table order_items` confirmed decimal price. No migration was pending in that check.

### 7. Tests and verification

Added tests:

- `tests/Feature/DiscountAndAdminAccessTest.php`: admin/storefront access, discount validation and pricing, coupon application, notification delivery/idempotency.
- `tests/Feature/DiscountActivationQueueTest.php`: notification fan-out only on new activation transitions.
- `tests/Feature/DiscountPaypalFlowTest.php`: quote and invalid-code responses, ignored client amount, server-priced orders with and without discounts, fractional prices, reservation/cancellation/expiry, capture and repeated webhook idempotency, webhook signature rejection, and unauthenticated webhook route behavior.

Verification actually run:

| Command | Result |
|---|---|
| Coupon/PayPal/customer-account feature tests (`DiscountAndAdminAccessTest`, `DiscountActivationQueueTest`, `DiscountPaypalFlowTest`) | 24 passed, 120 assertions |
| `php artisan test` | 36 passed, 1 failed, 240 assertions. The existing `ExampleTest` expects `GET /` to return 200, but no root route is registered and it returns 404. |
| `vendor/bin/pint --test` on the changed payment/discount/account PHP files | Passed |
| `php -l` on the migration and PayPal flow test | Passed |
| `php artisan route:list --path=paypal` and `--path=discount` | Confirmed listed routes |
| `php artisan view:cache` | Passed after customer account, notification, and admin report view changes |
| `php artisan migrate:status`, `php artisan db:table payments`, `php artisan db:table order_items` | Both migrations marked Ran; unique capture index and decimal item price confirmed |

PayPal was mocked with Mockery in the integration tests. No real PayPal order or payment was created. The one full-suite failure is the existing root-route expectation; no change to `/` was made here.

### Customer Account Coupons

There was no coupon listing in the customer account before this change. The existing account page showed profile details, order history, and a cart link. Available coupons now appear in a **My Coupons** section on the existing account page:

- Page and URL: `GET /profile`, route `profile.profilePage`, handled by `App\Http\Controllers\web\Profile\UserController::profilePage`; it remains inside the existing `auth` middleware group.
- Data source: `DiscountService::availableCoupons()` queries the `discounts` table and counts non-expired pending orders with one constrained relationship count. It does not trust client-supplied coupon identifiers.
- Eligibility: only active discounts whose start/end dates include the current time, whose settings are valid, and whose aggregate usage limit has capacity are listed. Pending PayPal orders reserve capacity using the same expiration window as checkout. The account list does not label a coupon as “Used” for an individual customer because the schema has no per-customer redemption record.
- Scope: discounts are global in the existing schema; there is no user assignment field. Activation notifications are sent to regular users, excluding admins, employees, and drivers. The account displays currently active global discounts to authenticated storefront users; it does not infer an individual “used” state from aggregate `usage_count`.
- Order minimum: a coupon can require a minimum subtotal, shown on its card in EGP. The account has no fixed cart subtotal, so it lists an otherwise-active coupon and communicates the minimum; applying it in the cart calls the existing `DiscountService::quote()` with the actual database-backed cart subtotal and reports a validation error if the minimum is not met. Payment creation revalidates again.
- Actions and states: **Copy code** uses the browser clipboard API and announces success or a manual-copy fallback. **Use coupon** links to the existing `/get-cart` page and passes the code as a query parameter to prefill the current discount input; the customer still applies it through the existing `POST /discount/apply` action. An empty account list has a dedicated empty state. The page is synchronous and uses the application's normal error handling if its data request fails.
- Activation notices already include the code in their message. They now also link to `GET /profile#my-coupons` and the cart; activation emails link to the same coupon section. Notification fan-out remains idempotent and excludes admins, employees, and drivers.

Files changed for this account feature (all existed; no files or migrations were created):

- `app/Services/DiscountService.php`
- `app/Http/Controllers/web/profile/UserController.php`
- `resources/views/web/profile/index.blade.php`
- `resources/views/web/cart/index.blade.php`
- `app/Observers/DiscountObserver.php`
- `resources/views/web/profile/notification.blade.php`
- `resources/views/emails/discount-activation.blade.php`
- `tests/Feature/DiscountAndAdminAccessTest.php`
- `README.md`

The latest focused run passed the account visibility, unavailable/empty state, authentication, cart-prefill, and notification-link cases as part of the combined discount, notification, and PayPal suite: **24 tests passed, 120 assertions**. `php artisan view:cache` also passed. No migration was added or required.

### Filament Admin Panel

The admin panel uses the installed Laravel 11.57 and Filament 5 stack at `/admin`. Resource and page discovery was already enabled in `AdminPanelProvider`; the existing Products, Orders, Users, Discounts, and Filament Shield Roles resources were not missing from discovery. The issue was authorization: Product, Order, User, and Role policies require Spatie permissions such as `ViewAny:Product`, but the existing `admin` role had not been granted them. The Discount policy allowed the `admin` role directly, so Discounts was the only visible resource. The categories and payments tables had models but no Filament resources; those resources and the reports page were added without adding schema.

`FilamentAdminPermissionsSeeder` now declares the managed permission names, while `RoleSeeder` creates the four existing application roles and assigns managed permissions. Neither seeder promotes an existing customer. The `admin` and `employee` roles may enter Filament; policies and page/widget authorization then restrict each role's routes and data. Ordinary customers and drivers are denied panel access.

#### Admin pages and actual routes

| Page/resource | Actual URL | Access and available operations |
|---|---|---|
| Dashboard | `/admin` | Admin panel; database-backed overview widget |
| Categories | `/admin/categories` | Admin: list, create, edit; deletion is denied to protect category relationships |
| Products | `/admin/products` | Admin: list, create, edit; Employee: list and view only |
| Orders | `/admin/orders` | Admin and Employee: read-only list and detail; employee view hides order totals, payment fields, email, and PayPal identifiers |
| Payments | `/admin/payments` | Admin: read-only list and detail |
| Users / customers | `/admin/users` | Admin: list, create, edit; no destructive delete action |
| Discounts / coupons | `/admin/discounts` | Admin: list, create, view, edit; `DiscountPolicy` requires the admin role |
| Roles and permissions | `/admin/shield/roles` | Admin: list, create, view, edit |
| Reports | `/admin/reports` | Admin only through the `View:AdminReports` permission |

The currently registered routes were confirmed with `php artisan route:list --path=admin` (25 routes). Product create/edit, Category create/edit, User create/edit, Discount create/view/edit, Payment view, Order view, and Shield role CRUD routes are registered. Orders and Payments have no create/edit routes because they are financial records. Navigation uses Catalog, Sales, Customers & Access, Marketing, Reports, and Shield role-management groups.

#### Dashboard and reports

`App\Services\Admin\AdminReportService` centralizes database-backed metrics. The admin dashboard is an executive overview with seven-day collected revenue by currency and prior-week comparison, order and completed-order counts, payment success and cancellation rates, order-status distribution, daily/weekly/monthly order and revenue trends, top products by completed-order quantity, latest orders with payment status, and alerts for coupons expiring soon or completed orders without a settled payment. Quick actions link to the existing admin resources.

Employees see the operational order, product, and order-status overview, plus non-financial order and product-quantity trends. Financial cards/charts, payment details, reports, coupon alerts, and admin quick actions are restricted to users with the corresponding admin permissions.

For reporting, `completed` is the current successful PayPal capture status; `paid` is accepted as a legacy settled status because it exists in project data and factories. Revenue counts only the latest successful payment record per order and keeps currencies separate. Pending and failed attempts are excluded from revenue. Payment success rate is based on unique orders with payment attempts in the selected period, not a raw attempt count. The dashboard flags completed orders with no settled payment rather than rewriting inconsistent legacy/demo records.

`App\Filament\Pages\Reports` at `/admin/reports` defaults to the latest 30 calendar days including today and limits the selected range to 366 days. Filters include dates, order status, payment status, payment method, product category, coupon, and chart interval; changes are applied through an explicit Apply action, and Reset restores the default range. The page includes KPI cards, revenue/order/status charts, grouped payment details, product and category unit sales, customer activity, coupon usage and discount totals by recorded currency, and a searchable/paginated order table. Export orders downloads a CSV using the same date, status, method, category, coupon, and permission scope as the report.

The Filament Tailwind theme is built from `resources/css/filament/admin/theme.css`, which imports Filament's theme and scans `app/Filament` and `resources/views/filament`; it is included in the Vite input and attached to the Admin panel. This is separate from the storefront stylesheet.

Order-item values are not profit or collected revenue. The database does not contain inventory quantities, product cost, refunds, or a reliable shipment-cost field, and `order_items` does not carry a dependable currency. Product and category reports therefore show units only; no low-stock, profit, refund, or EGP product-value figures are invented. Payment attempt tables may show recorded attempt amounts grouped by their own status/method/currency; only settled attempts contribute to collected revenue. Coupon `usage_count` is aggregate, not a per-customer redemption record.

#### Files created and updated for the admin panel

Created:

- `app/Filament/Resources/Categories/CategoryResource.php`, `Schemas/CategoryForm.php`, `Tables/CategoriesTable.php`, and `Pages/CreateCategory.php`, `Pages/EditCategory.php`, `Pages/ListCategories.php`.
- `app/Filament/Resources/Payments/PaymentResource.php`, `Schemas/PaymentInfolist.php`, `Tables/PaymentsTable.php`, and `Pages/ListPayments.php`, `Pages/ViewPayment.php`.
- `app/Filament/Pages/Reports.php`; widgets `AdminOverview.php`, `EmployeeOverview.php`, `OrdersTrendChart.php`, `AdminSalesTrendChart.php`, `AdminDashboardAlerts.php`, `AdminOrderStatusChart.php`, `AdminTopProductsChart.php`, `ReportChartWidget.php`, `ReportsRevenueChart.php`, `ReportsOrdersChart.php`, `ReportsOrderStatusChart.php`, `ReportsPaymentStatusChart.php`, `RecentOrders.php`, `RecentCustomers.php`, `ReportOrdersTable.php`, and `AdminQuickActions.php`; `app/Services/Admin/AdminReportService.php`; `resources/views/filament/pages/reports.blade.php`; `resources/views/filament/widgets/admin-dashboard-alerts.blade.php`; and `resources/views/filament/widgets/admin-quick-actions.blade.php`.
- Policies `app/Policies/CategoryPolicy.php`, `DiscountPolicy.php`, `OrderPolicy.php`, `PaymentPolicy.php`, `ProductPolicy.php`, `RolePolicy.php`, and `UserPolicy.php`; new seeders `database/seeders/FilamentAdminPermissionsSeeder.php` and `InitialAdminSeeder.php`; config files `config/filament-shield.php`, `demo.php`, and `initial-admin.php`; and `tests/Feature/FilamentAdminPanelTest.php`.

Updated:

- `app/Providers/Filament/AdminPanelProvider.php` registers the Filament theme and role-appropriate dashboard widgets. `resources/css/filament/admin/theme.css` and `vite.config.js` register the theme with Vite.
- Existing Product files: `app/Filament/Resources/Products/ProductResource.php`, `Schemas/ProductForm.php`, and `Tables/ProductsTable.php`.
- Existing Order files: `app/Filament/Resources/Orders/OrderResource.php`, `Pages/ListOrders.php`, `Pages/ViewOrder.php`, `Schemas/OrderInfolist.php`, and `Tables/OrdersTable.php`.
- Existing User files: `app/Filament/Resources/Users/UserResource.php`, `Pages/EditUser.php`, `Schemas/UserForm.php`, and `Tables/UsersTable.php`.
- `app/Models/User.php` permits only admin/employee panel entry; `app/Models/Order.php` adds `latestPayment()` for recent-order reporting; order forms/tables/infolists apply the role-specific privacy and read-only rules.
- `database/seeders/DatabaseSeeder.php`, `RoleSeeder.php`, `UsersSeeder.php`, `CategoriesSeeder.php`, `ProductSeeder.php`, `OrdersSeeder.php`, and `PaymentsSeeder.php`; `.env.example`; and `app/Jobs/DispatchDiscountActivationNotifications.php` implement role setup, opt-in demo seeding, explicit initial-admin configuration, and exclusion of employee accounts from customer coupon notifications.
- The existing Discounts resource is retained in its Marketing navigation group; it was not duplicated by this work.

No migration or dependency was added for the Filament work.

#### Roles, permissions, and safe seeding

`RoleSeeder` is repeatable: it creates `admin`, `employee`, `user`, and `driver` roles if missing and reconciles only permissions managed by `FilamentAdminPermissionsSeeder`. Unmanaged/custom permissions are retained. The admin permissions cover Product CRUD except delete, Order view, User CRUD except delete, Category CRUD, Payment view, Shield Role CRUD, `View:AdminDashboard`, and `View:AdminReports`. Discount access remains protected by `DiscountPolicy`'s admin-role check. The managed employee permissions are `ViewAny:Product`, `View:Product`, `ViewAny:Order`, `View:Order`, and `View:OperationalDashboard`; no managed report or financial/resource-management permissions are granted. The Shield Roles form exposes the three custom dashboard/report permissions so an authorized administrator can manage them. Any unrelated pre-existing custom permissions remain untouched by the seeder.

`DatabaseSeeder` always calls the role and optional user seeders. Demo accounts/data are off by default and never seeded in production. To set up roles/permissions (and run any other enabled safe seeders) after applying migrations:

```sh
php artisan db:seed --force
```

To bootstrap the first administrator, put unique, private values in the local environment for `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_PHONE`, and `INITIAL_ADMIN_PASSWORD`, then run the explicit seeder:

```sh
php artisan db:seed --class="Database\Seeders\InitialAdminSeeder" --force
```

The initial-admin seeder is repeatable; it exits if an admin already exists and refuses to promote or overwrite an existing non-admin account. Remove the one-time values from the environment after successful setup. Never commit them.

Optional local demo data requires `DEMO_DATA_ENABLED=true` plus unique `DEMO_ADMIN_*` and `DEMO_EMPLOYEE_*` email, phone, and password values. The demo user seeder only creates missing accounts and never changes the role, password, or profile of an existing matching email. Database sample data is seeded only outside production and only when the core category/product/order/payment tables are empty. Demo seeders are not a production account provisioning mechanism.

#### Admin testing and known limitations

Verification in the current worktree:

| Command | Result |
|---|---|
| `php artisan test --filter=FilamentAdminPanelTest` | **12 passed, 125 assertions** |
| `php artisan test --filter='DiscountAndAdminAccessTest\|DiscountActivationQueueTest\|DiscountPaypalFlowTest'` | **24 passed, 120 assertions** |
| `php artisan test` | **37 passed, 1 failed, 247 assertions**. The unrelated existing `ExampleTest` expects `GET /` to return 200; no root route is registered, so it returns 404. |
| `php artisan view:cache` | Passed |
| `php artisan route:list --path=admin` | Passed; 25 routes registered |
| `vendor/bin/pint` on changed admin PHP files | Passed |
| `npm run build` | Passed; the generated Vite manifest includes `resources/css/filament/admin/theme.css` |

The admin tests exercise admin/employee/customer access, direct URL restrictions, customer-field/financial-data hiding for employees, role/permission seeder idempotency, demo-user non-promotion, initial-admin provisioning, empty dashboard/report states, applied category/payment filters, CSV download, real payment calculations, duplicate-payment exclusion, and currency separation. No real database seed run or browser test was performed. `git diff --check` reports whitespace only in unrelated in-progress changes to `LocationController`, `location-tracker.blade.php`, and `routes/channels.php`; those files were not changed for this redesign.

### 8. Remaining tasks, limitations, and manual configuration

**Completed**

- Filament discount management, storefront coupon entry, server-side pricing, API quote and payment creation, signed webhook verification, successful-use accounting, and duplicate-capture protection.
- Filament admin resources and role-based navigation for existing catalog, order, customer, payment, discount, and role data; database-backed dashboard/report pages; and the admin/employee permission model.
- Idempotent role and permission seeders plus an explicit first-admin bootstrap path and opt-in non-production demo accounts.
- The two migrations are applied in the database checked for this report.

**Partially completed**

- The JSON quote endpoint is available to authenticated API clients but is not wired to a separate mobile/API frontend in this repository. The storefront currently uses its standard form/session flow.
- API quote and API payment creation currently support one `product_id` with a quantity per request. The storefront web flow handles the whole server-side cart.
- Pending coupon reservations expire after `PAYPAL_ORDER_EXPIRATION_MINUTES` (default `30`). Old pending orders are marked cancelled when a later locked quote for that coupon performs cleanup, or when capture of that expired order is attempted. There is no scheduled global cleanup job.
- The API success/cancel callbacks are public and operate using the PayPal token; they are not behind `auth:sanctum`. Keep callback URLs and token handling aligned with PayPal's expected flow.

**Not implemented**

- Per-customer coupon usage limits; only an aggregate coupon limit exists.
- EGP-to-USD currency conversion and inclusion of the displayed EGP delivery fee in the PayPal amount. These require an explicit business currency/fee policy before implementation.
- The pre-existing `/` route expected by `ExampleTest`.

**Requires manual configuration**

- Set `PAYPAL_MODE`, the appropriate sandbox/live client ID and secret, and `PAYPAL_WEBHOOK_ID` in `.env`; all credential examples are blank in `.env.example`.
- Register `POST https://<your-domain>/api/paypal/webhook` with PayPal and copy its webhook ID into `PAYPAL_WEBHOOK_ID`.
- Configure the mail transport for discount activation email. Configure TLS/public HTTPS and confirm the amount currency/fee policy before accepting production transactions.
- Run `php artisan optimize:clear` after changing environment configuration.

### 9. Change summary from Git

At the current Git status check, this worktree had **101 changed paths: 70 tracked modified files and 31 untracked files**. This is the aggregate in-progress worktree, including earlier storefront, authentication, coupon, PayPal, customer-account, broadcasting, and admin changes; it must not be attributed solely to the Filament admin work. The following inventory preserves the earlier coupon/PayPal/customer-account scope; the Filament-specific files are documented in the preceding section. No staged changes were reported.

The scoped files were:

- Modified: `.env.example`, `README.md`, `app/Http/Controllers/PaypalController.php`, `app/Http/Controllers/web/Auth/AuthController.php`, `app/Http/Controllers/web/NotificationController.php`, `app/Http/Controllers/web/cart/CartController.php`, `app/Http/Controllers/web/payment/PaypalController.php`, `app/Http/Controllers/web/profile/UserController.php`, `app/Models/Order.php`, `app/Models/Payment.php`, `app/Models/User.php`, `app/Notifications/GlobalNotificationMail.php`, `app/Providers/AppServiceProvider.php`, `app/Providers/Filament/AdminPanelProvider.php`, `app/Services/Payment/PaypalService.php`, `config/paypal.php`, `database/seeders/UsersSeeder.php`, `resources/views/web/cart/index.blade.php`, `resources/views/web/payment/cancel.blade.php`, `resources/views/web/payment/failed.blade.php`, `resources/views/web/payment/success.blade.php`, `resources/views/web/profile/index.blade.php`, `resources/views/web/profile/notification.blade.php`, `routes/api.php`, and `routes/web.php`.
- Created: `app/Filament/Resources/Discounts/DiscountResource.php`, `app/Filament/Resources/Discounts/Pages/CreateDiscount.php`, `EditDiscount.php`, `ListDiscounts.php`, `ViewDiscount.php`, `app/Filament/Resources/Discounts/Schemas/DiscountForm.php`, `DiscountInfolist.php`, `app/Filament/Resources/Discounts/Tables/DiscountsTable.php`, `app/Http/Controllers/DiscountQuoteController.php`, `app/Http/Controllers/web/DiscountController.php`, `app/Http/Middleware/EnsureRoleIsAdminOrDriver.php`, `app/Http/Requests/CreatePaypalPaymentRequest.php`, `app/Http/Requests/QuoteDiscountRequest.php`, `app/Jobs/DispatchDiscountActivationNotifications.php`, `app/Jobs/SendDiscountActivationEmail.php`, `app/Mail/DiscountActivationMail.php`, `app/Models/Discount.php`, `app/Observers/DiscountObserver.php`, `app/Policies/DiscountPolicy.php`, `app/Policies/OrderPolicy.php`, `app/Policies/ProductPolicy.php`, `app/Policies/RolePolicy.php`, `app/Policies/UserPolicy.php`, `app/Services/DiscountService.php`, `config/filament-shield.php`, `database/migrations/2026_10_25_190000_create_discounts_table.php`, `database/migrations/2026_10_25_204500_add_paypal_capture_id_and_order_item_price_precision.php`, `resources/views/emails/discount-activation.blade.php`, `tests/Feature/DiscountActivationQueueTest.php`, `tests/Feature/DiscountAndAdminAccessTest.php`, and `tests/Feature/DiscountPaypalFlowTest.php`.

For local verification, run `php artisan migrate:status`, `php artisan route:list --path=discount`, `php artisan route:list --path=paypal`, and `php artisan test --filter='DiscountAndAdminAccessTest|DiscountActivationQueueTest|DiscountPaypalFlowTest'`. To manually exercise the complete web flow, create and activate a test coupon in `/admin/discounts`, add products to an authenticated storefront cart, apply the code, compare the displayed discount with the order snapshot, continue to PayPal sandbox, then complete or cancel the payment and verify the order/payment/usage records.

### Reverb and location broadcasting

This project uses Reverb. The client connection (`REVERB_HOST`, `REVERB_PORT`, and `REVERB_SCHEME`) must point to the machine running the Reverb server. For a local development server, the server listens on `REVERB_SERVER_HOST=0.0.0.0` and `REVERB_SERVER_PORT=9000`, while the browser and queue worker connect to `127.0.0.1:9000` over HTTP. Provide the Reverb application ID, key, and secret through environment configuration.

Start the Reverb server in a separate terminal:

```sh
php artisan reverb:start
```

On Windows, verify the local listener with:

```powershell
Test-NetConnection 127.0.0.1 -Port 9000
```

If Reverb or its host/port configuration changes, clear cached configuration and restart the queue worker so queued broadcast jobs load the current settings:

```sh
php artisan optimize:clear
php artisan queue:restart
```

Start a replacement `php artisan queue:work --tries=5` process after the restart. If broadcasts failed while Reverb was unavailable, first verify that the server is listening, then inspect `php artisan queue:failed` and retry the affected job IDs. Location broadcasts are queued separately from order creation; a Reverb outage should be diagnosed through the failed-job records rather than by disabling broadcasting.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
