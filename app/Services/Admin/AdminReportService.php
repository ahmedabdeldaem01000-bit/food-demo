<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    private const SUCCESSFUL_PAYMENT_STATUSES = ['paid', 'completed'];

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $now = CarbonImmutable::now();
        $from = $now->subDays(6)->startOfDay();
        $previousFrom = $from->subDays(7);
        $previousTo = $from->subSecond();
        $weekRevenue = $this->revenueByCurrency($from, $now);
        $previousWeekRevenue = $this->revenueByCurrency($previousFrom, $previousTo);
        $orderCounts = $this->orderCounts($from, $now);
        $previousOrderCounts = $this->orderCounts($previousFrom, $previousTo);
        $paymentMetrics = $this->paymentSuccessMetrics($from, $now);
        $comparisonCurrencies = $weekRevenue->pluck('currency')
            ->merge($previousWeekRevenue->pluck('currency'))
            ->unique()
            ->sort()
            ->values()
            ->map(function (string $currency) use ($weekRevenue, $previousWeekRevenue): array {
                $current = (float) ($weekRevenue->firstWhere('currency', $currency)->total ?? 0);
                $previous = (float) ($previousWeekRevenue->firstWhere('currency', $currency)->total ?? 0);

                return [
                    'currency' => $currency,
                    'total' => $current,
                    'previous_total' => $previous,
                    'change_percent' => $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null,
                ];
            })
            ->all();

        return [
            'revenue_7d' => $weekRevenue,
            'previous_revenue_7d' => $previousWeekRevenue,
            'revenue_comparison_7d' => $comparisonCurrencies,
            'orders_7d' => $orderCounts['total'],
            'previous_orders_7d' => $previousOrderCounts['total'],
            'completed_orders_7d' => $orderCounts['completed'],
            'cancelled_orders_7d' => $orderCounts['cancelled'],
            'cancel_rate_7d' => $orderCounts['total'] === 0 ? 0 : round($orderCounts['cancelled'] / $orderCounts['total'] * 100, 1),
            'order_change_percent_7d' => $previousOrderCounts['total'] > 0
                ? round(($orderCounts['total'] - $previousOrderCounts['total']) / $previousOrderCounts['total'] * 100, 1)
                : null,
            'order_statuses_7d' => $this->orderStatusDistribution($from, $now),
            'successful_payment_orders_7d' => $paymentMetrics['successful_orders'],
            'payment_orders_attempted_7d' => $paymentMetrics['attempted_orders'],
            'payment_success_rate_7d' => $paymentMetrics['success_rate'],
        ];
    }

    /**
     * @return array{expiring_discounts: int, completed_orders_without_settled_payment: int}
     */
    public function dashboardAlerts(): array
    {
        $now = CarbonImmutable::now();

        return [
            'expiring_discounts' => Discount::query()
                ->where('is_active', true)
                ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->whereBetween('ends_at', [$now, $now->addDays(7)])
                ->count(),
            'completed_orders_without_settled_payment' => Order::query()
                ->where('status', 'completed')
                ->whereDoesntHave('payment', fn (Builder $query) => $this->whereSuccessfulStatus($query))
                ->count(),
        ];
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<float>}>}
     */
    public function revenueTrend(
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $groupBy = 'day',
        ?string $paymentMethod = null,
        ?int $discountId = null,
        ?string $paymentStatus = null,
        ?int $categoryId = null
    ): array {
        $groupBy = $this->validGrouping($groupBy);
        $labels = $this->trendLabels($from, $to, $groupBy);

        $rows = $this->successfulPayments($from, $to, $paymentStatus, $paymentMethod, $discountId, $categoryId)
            ->selectRaw('DATE(created_at) as payment_date')
            ->selectRaw('UPPER(currency) as currency')
            ->selectRaw('SUM(amount) as collected')
            ->groupByRaw('DATE(created_at)')
            ->groupByRaw('UPPER(currency)')
            ->orderBy('payment_date')
            ->get();

        $amounts = [];
        foreach ($rows as $row) {
            $bucket = $this->trendBucket(CarbonImmutable::parse($row->payment_date), $groupBy);
            $amounts[$row->currency][$bucket] = ($amounts[$row->currency][$bucket] ?? 0) + (float) $row->collected;
        }

        $datasets = collect(array_keys($amounts))
            ->map(fn (string $currency): array => [
                'label' => $currency,
                'data' => collect($labels)
                    ->map(fn (string $label): float => (float) ($amounts[$currency][$label] ?? 0))
                    ->all(),
            ])
            ->all();

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>}
     */
    public function ordersTrend(
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $groupBy = 'day',
        ?string $orderStatus = null,
        ?int $discountId = null,
        ?int $categoryId = null,
        ?string $paymentStatus = null,
        ?string $paymentMethod = null
    ): array {
        $groupBy = $this->validGrouping($groupBy);
        $labels = $this->trendLabels($from, $to, $groupBy);

        $rows = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($orderStatus, fn (Builder $query) => $query->where('status', $orderStatus))
            ->when($discountId, fn (Builder $query) => $query->where('discount_id', $discountId))
            ->when($categoryId, fn (Builder $query) => $query->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId)))
            ->when($paymentStatus || $paymentMethod, fn (Builder $query) => $query->whereHas(
                'payment',
                fn (Builder $query) => $query
                    ->when($paymentStatus, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($paymentStatus)]))
                    ->when($paymentMethod, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($paymentMethod)]))
            ))
            ->selectRaw('DATE(created_at) as order_date')
            ->selectRaw('COUNT(*) as order_count')
            ->groupByRaw('DATE(created_at)')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $bucket = $this->trendBucket(CarbonImmutable::parse($row->order_date), $groupBy);
            $counts[$bucket] = ($counts[$bucket] ?? 0) + (int) $row->order_count;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Orders',
                'data' => collect($labels)
                    ->map(fn (string $label): int => (int) ($counts[$label] ?? 0))
                    ->all(),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function report(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?string $orderStatus = null,
        ?string $paymentStatus = null,
        ?string $paymentMethod = null,
        ?int $categoryId = null,
        ?int $discountId = null,
        string $groupBy = 'day'
    ): array {
        $dateOrders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($discountId, fn (Builder $query) => $query->where('discount_id', $discountId))
            ->when($categoryId, fn (Builder $query) => $query->whereHas(
                'items.product',
                fn (Builder $query) => $query->where('category_id', $categoryId)
            ))
            ->when($paymentStatus || $paymentMethod, fn (Builder $query) => $query->whereHas(
                'payment',
                fn (Builder $query) => $query
                    ->when($paymentStatus, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($paymentStatus)]))
                    ->when($paymentMethod, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($paymentMethod)]))
            ));
        $ordersInPeriod = (clone $dateOrders)
            ->when($orderStatus, fn (Builder $query) => $query->where('status', $orderStatus));
        $successfulPayments = $this->successfulPayments(
            $from,
            $to,
            $paymentStatus,
            $paymentMethod,
            $discountId,
            $categoryId,
        );
        $paymentRows = $this->paymentRows($from, $to, $paymentStatus, $paymentMethod, $discountId, $categoryId);
        $periodDays = max(1, (int) $from->startOfDay()->diffInDays($to->startOfDay()) + 1);
        $previousTo = $from->startOfDay()->subSecond();
        $previousFrom = $previousTo->subDays($periodDays)->addSecond();
        $paymentMetrics = $this->paymentSuccessMetrics($from, $to, $paymentMethod, $discountId, $paymentStatus, $categoryId);
        $completedOrderIds = (clone $ordersInPeriod)->where('status', 'completed')->select('id');
        $completedProductSales = $this->completedProductSales($from, $to, $categoryId, $discountId, $orderStatus)
            ->whereIn('orders.id', clone $completedOrderIds);

        $topProducts = (clone $completedProductSales)
            ->select('products.id', 'products.name')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->limit(10)
            ->get();

        $categorySales = (clone $completedProductSales)
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->select('categories.id', 'categories.name')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('units_sold')
            ->limit(10)
            ->get();

        $topCustomers = User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'driver', 'employee']))
            ->whereIn('id', (clone $ordersInPeriod)->whereNotNull('user_id')->select('user_id'))
            ->withCount([
                'order as orders_in_period_count' => fn (Builder $query) => $query
                    ->whereIn('orders.id', (clone $ordersInPeriod)->select('id')),
            ])
            ->orderByDesc('orders_in_period_count')
            ->limit(10)
            ->get(['id', 'name']);

        $productsWithoutSales = Product::query()
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->whereNotIn('id', (clone $completedProductSales)->select('order_items.product_id')->distinct())
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        $discountUsage = Discount::query()
            ->when($discountId, fn (Builder $query) => $query->whereKey($discountId))
            ->withCount([
                'orders as successful_orders_count' => fn (Builder $query) => $query
                    ->whereIn('orders.id', clone $completedOrderIds),
            ])
            ->when($orderStatus && $orderStatus !== 'completed', fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('successful_orders_count')
            ->limit(10)
            ->get(['id', 'name', 'code', 'usage_count', 'usage_limit', 'is_active', 'starts_at', 'ends_at']);

        $discountTotalsByCurrency = Order::query()
            ->whereIn('id', clone $completedOrderIds)
            ->whereNotNull('discount_id')
            ->select('discount_id')
            ->selectRaw('UPPER(currency) as currency')
            ->selectRaw('SUM(discount_amount) as discount_total')
            ->groupBy('discount_id')
            ->groupByRaw('UPPER(currency)')
            ->get();

        $discountTotalsByCurrency = $discountTotalsByCurrency->groupBy('discount_id');

        $customerOrderCounts = (clone $ordersInPeriod)
            ->whereNotNull('user_id')
            ->whereIn('user_id', User::query()
                ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'driver', 'employee']))
                ->select('id'))
            ->select('user_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('user_id');

        $orderStatuses = $this->orderStatusDistribution($from, $to, $orderStatus, $discountId, $categoryId, $paymentStatus, $paymentMethod);

        $paymentStatuses = (clone $paymentRows)
            ->selectRaw('LOWER(status) as status')
            ->selectRaw('LOWER(method) as method')
            ->selectRaw('UPPER(currency) as currency')
            ->selectRaw('COUNT(*) as payment_count')
            ->selectRaw('SUM(amount) as recorded_amount')
            ->groupByRaw('LOWER(status)')
            ->groupByRaw('LOWER(method)')
            ->groupByRaw('UPPER(currency)')
            ->orderBy('status')
            ->orderBy('method')
            ->get();

        $dailyRevenue = (clone $successfulPayments)
            ->selectRaw('DATE(created_at) as payment_date')
            ->selectRaw('UPPER(currency) as currency')
            ->selectRaw('SUM(amount) as collected')
            ->groupByRaw('DATE(created_at)')
            ->groupByRaw('UPPER(currency)')
            ->orderBy('payment_date')
            ->get();

        $attempts = (clone $paymentRows)->count();
        $completedOrders = (clone $ordersInPeriod)->where('status', 'completed')->count();
        $cancelledOrders = (clone $ordersInPeriod)->whereIn('status', ['cancelled', 'canceled'])->count();
        $ordersInRangeCount = (clone $ordersInPeriod)->count();
        $successRate = $paymentMetrics['success_rate'];
        $currentRevenue = $this->revenueByCurrency($from, $to, $paymentMethod, $discountId, $categoryId, $paymentStatus);
        $previousRevenue = $this->revenueByCurrency($previousFrom, $previousTo, $paymentMethod, $discountId, $categoryId, $paymentStatus);
        $averageOrderValue = (clone $successfulPayments)
            ->selectRaw('UPPER(currency) as currency')
            ->selectRaw('AVG(amount) as average_amount')
            ->selectRaw('COUNT(*) as paid_orders')
            ->groupByRaw('UPPER(currency)')
            ->orderBy('currency')
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'orders_total' => $ordersInPeriod->count(),
            'orders_completed' => $completedOrders,
            'orders_cancelled' => $cancelledOrders,
            'orders_in_range_count' => $ordersInRangeCount,
            'order_statuses' => $orderStatuses,
            'revenue_by_currency' => $currentRevenue,
            'previous_revenue_by_currency' => $previousRevenue,
            'previous_orders_total' => Order::query()
                ->whereBetween('created_at', [$previousFrom, $previousTo])
                ->when($orderStatus, fn (Builder $query) => $query->where('status', $orderStatus))
                ->when($discountId, fn (Builder $query) => $query->where('discount_id', $discountId))
                ->when($categoryId, fn (Builder $query) => $query->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId)))
                ->when($paymentStatus || $paymentMethod, fn (Builder $query) => $query->whereHas(
                    'payment',
                    fn (Builder $query) => $query
                        ->when($paymentStatus, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($paymentStatus)]))
                        ->when($paymentMethod, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($paymentMethod)]))
                ))
                ->count(),
            'revenue_by_method' => (clone $successfulPayments)
                ->selectRaw('LOWER(method) as method')
                ->selectRaw('UPPER(currency) as currency')
                ->selectRaw('SUM(amount) as collected')
                ->groupByRaw('LOWER(method)')
                ->groupByRaw('UPPER(currency)')
                ->orderBy('method')
                ->get(),
            'average_order_value_by_currency' => $averageOrderValue,
            'discount_totals_by_currency' => $discountTotalsByCurrency,
            'payment_statuses' => $paymentStatuses,
            'payment_attempts' => $attempts,
            'successful_payment_orders' => $paymentMetrics['successful_orders'],
            'payment_orders_attempted' => $paymentMetrics['attempted_orders'],
            'payment_success_rate' => $successRate,
            'order_status_options' => Order::query()->distinct()->orderBy('status')->pluck('status'),
            'payment_status_options' => Payment::query()
                ->selectRaw('LOWER(status) as status')
                ->groupByRaw('LOWER(status)')
                ->orderBy('status')
                ->pluck('status'),
            'payment_method_options' => Payment::query()
                ->selectRaw('LOWER(method) as method')
                ->groupByRaw('LOWER(method)')
                ->orderBy('method')
                ->pluck('method'),
            'category_options' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'discount_options' => Discount::query()->orderBy('code')->pluck('code', 'id'),
            'daily_revenue' => $dailyRevenue,
            'top_products' => $topProducts,
            'category_sales' => $categorySales,
            'products_without_sales' => $productsWithoutSales,
            'customers_total' => User::query()
                ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'driver', 'employee']))
                ->count(),
            'new_customers' => User::query()
                ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'driver', 'employee']))
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'top_customers_by_orders' => $topCustomers,
            'customers_with_orders' => DB::query()
                ->fromSub(clone $customerOrderCounts, 'customer_orders')
                ->count(),
            'repeat_customers' => DB::query()
                ->fromSub(clone $customerOrderCounts, 'customer_orders')
                ->where('orders_count', '>', 1)
                ->count(),
            'discount_usage' => $discountUsage,
            'active_discounts' => Discount::query()
                ->when($discountId, fn (Builder $query) => $query->whereKey($discountId))
                ->where('is_active', true)
                ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->count(),
            'expired_discounts' => Discount::query()
                ->when($discountId, fn (Builder $query) => $query->whereKey($discountId))
                ->where('ends_at', '<', now())
                ->count(),
            'inactive_discounts' => Discount::query()
                ->when($discountId, fn (Builder $query) => $query->whereKey($discountId))
                ->where('is_active', false)
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->count(),
            'scheduled_discounts' => Discount::query()
                ->when($discountId, fn (Builder $query) => $query->whereKey($discountId))
                ->where('is_active', true)
                ->where('starts_at', '>', now())
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->count(),
            'expiring_discounts' => Discount::query()
                ->when($discountId, fn (Builder $query) => $query->whereKey($discountId))
                ->where('is_active', true)
                ->whereBetween('ends_at', [now(), now()->addDays(7)])
                ->count(),
            'orders_trend' => $this->ordersTrend($from, $to, $groupBy, $orderStatus, $discountId, $categoryId, $paymentStatus, $paymentMethod),
            'revenue_trend' => $this->revenueTrend($from, $to, $groupBy, $paymentMethod, $discountId, $paymentStatus, $categoryId),
        ];
    }

    public function ordersForReport(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?string $orderStatus = null,
        ?string $paymentStatus = null,
        ?string $paymentMethod = null,
        ?int $discountId = null,
        ?int $categoryId = null
    ): Builder {
        return Order::query()
            ->with([
                'latestPayment',
                'user:id,name',
                'discount:id,code',
            ])
            ->whereBetween('created_at', [$from, $to])
            ->when($orderStatus, fn (Builder $query) => $query->where('status', $orderStatus))
            ->when($discountId, fn (Builder $query) => $query->where('discount_id', $discountId))
            ->when($categoryId, fn (Builder $query) => $query->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId)))
            ->when($paymentStatus || $paymentMethod, fn (Builder $query) => $query->whereHas(
                'payment',
                fn (Builder $paymentQuery) => $paymentQuery
                    ->whereBetween('created_at', [$from, $to])
                    ->when($paymentStatus, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($paymentStatus)]))
                    ->when($paymentMethod, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($paymentMethod)]))
            ))
            ->latest('created_at');
    }

    /**
     * Return one successful payment record per order. "paid" is retained as a
     * legacy settled status; new PayPal captures use "completed".
     */
    private function successfulPayments(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
        ?string $status = null,
        ?string $method = null,
        ?int $discountId = null,
        ?int $categoryId = null
    ): Builder {
        if ($status !== null && ! in_array(strtolower($status), self::SUCCESSFUL_PAYMENT_STATUSES, true)) {
            return Payment::query()->whereRaw('1 = 0');
        }

        $latestSuccessfulPaymentPerOrder = Payment::query()
            ->selectRaw('MAX(id)')
            ->whereRaw('LOWER(status) IN (?, ?)', self::SUCCESSFUL_PAYMENT_STATUSES)
            ->groupBy('order_id');

        $query = Payment::query()
            ->whereIn('id', $latestSuccessfulPaymentPerOrder)
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('created_at', '<=', $to))
            ->when($status, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($status)]))
            ->when($method, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($method)]))
            ->when($discountId, fn (Builder $query) => $query->whereIn(
                'order_id',
                Order::query()->select('id')->where('discount_id', $discountId)
            ));

        if ($categoryId !== null) {
            $query->whereIn('order_id', Order::query()
                ->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId))
                ->select('id'));
        }

        return $query;
    }

    /**
     * @return Collection<int, object>
     */
    private function revenueByCurrency(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
        ?string $method = null,
        ?int $discountId = null,
        ?int $categoryId = null,
        ?string $status = null
    ): Collection {
        return $this->successfulPayments($from, $to, $status, $method, $discountId, $categoryId)
            ->selectRaw('UPPER(currency) as currency')
            ->selectRaw('SUM(amount) as total')
            ->groupByRaw('UPPER(currency)')
            ->orderBy('currency')
            ->get();
    }

    /**
     * @return array{attempted_orders: int, successful_orders: int, success_rate: float}
     */
    private function paymentSuccessMetrics(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
        ?string $method = null,
        ?int $discountId = null,
        ?string $status = null,
        ?int $categoryId = null
    ): array {
        $attempts = Payment::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('created_at', '<=', $to))
            ->when($method, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($method)]))
            ->when($status, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($status)]))
            ->when($discountId, fn (Builder $query) => $query->whereIn(
                'order_id',
                Order::query()->select('id')->where('discount_id', $discountId)
            ));
        if ($categoryId !== null) {
            $attempts->whereIn('order_id', Order::query()
                ->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId))
                ->select('id'));
        }
        $attemptedOrders = (clone $attempts)->distinct()->count('order_id');
        $successfulOrders = $this->successfulPayments($from, $to, $status, $method, $discountId, $categoryId)->count();

        return [
            'attempted_orders' => $attemptedOrders,
            'successful_orders' => $successfulOrders,
            'success_rate' => $attemptedOrders === 0 ? 0 : round($successfulOrders / $attemptedOrders * 100, 1),
        ];
    }

    private function paymentRows(
        ?\DateTimeInterface $from,
        ?\DateTimeInterface $to,
        ?string $status,
        ?string $method,
        ?int $discountId,
        ?int $categoryId = null
    ): Builder {
        return Payment::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('created_at', '<=', $to))
            ->when($status, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($status)]))
            ->when($method, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($method)]))
            ->when($discountId, fn (Builder $query) => $query->whereIn(
                'order_id',
                Order::query()->select('id')->where('discount_id', $discountId)
            ))
            ->when($categoryId, fn (Builder $query) => $query->whereIn(
                'order_id',
                Order::query()->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId))->select('id')
            ));
    }

    private function whereSuccessfulStatus(Builder $query): Builder
    {
        return $query->whereRaw('LOWER(status) IN (?, ?)', self::SUCCESSFUL_PAYMENT_STATUSES);
    }

    /**
     * @return array{total: int, completed: int, cancelled: int}
     */
    private function orderCounts(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?int $discountId = null,
        ?string $status = null
    ): array {
        $counts = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($discountId, fn (Builder $query) => $query->where('discount_id', $discountId))
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', ['completed'])
            ->selectRaw('SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as cancelled', ['cancelled', 'canceled'])
            ->first();

        return [
            'total' => (int) $counts->total,
            'completed' => (int) $counts->completed,
            'cancelled' => (int) $counts->cancelled,
        ];
    }

    public function orderStatusDistribution(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?string $status = null,
        ?int $discountId = null,
        ?int $categoryId = null,
        ?string $paymentStatus = null,
        ?string $paymentMethod = null
    ): Collection {
        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($discountId, fn (Builder $query) => $query->where('discount_id', $discountId))
            ->when($categoryId, fn (Builder $query) => $query->whereHas('items.product', fn (Builder $query) => $query->where('category_id', $categoryId)))
            ->when($paymentStatus || $paymentMethod, fn (Builder $query) => $query->whereHas(
                'payment',
                fn (Builder $query) => $query
                    ->when($paymentStatus, fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [strtolower($paymentStatus)]))
                    ->when($paymentMethod, fn (Builder $query) => $query->whereRaw('LOWER(method) = ?', [strtolower($paymentMethod)]))
            ))
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();
    }

    public function paymentStatusDistribution(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?string $status = null,
        ?string $method = null,
        ?int $discountId = null,
        ?int $categoryId = null
    ): Collection {
        return $this->paymentRows($from, $to, $status, $method, $discountId, $categoryId)
            ->selectRaw('LOWER(status) as status')
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw('LOWER(status)')
            ->orderBy('status')
            ->get();
    }

    private function completedProductSales(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?int $categoryId = null,
        ?int $discountId = null,
        ?string $orderStatus = null
    ): Builder {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$from, $to])
            ->when($categoryId, fn (Builder $query) => $query->where('products.category_id', $categoryId))
            ->when($discountId, fn (Builder $query) => $query->where('orders.discount_id', $discountId))
            ->when($orderStatus && $orderStatus !== 'completed', fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    /**
     * @return Collection<int, object>
     */
    public function topProducts(
        CarbonImmutable $from,
        CarbonImmutable $to,
        int $limit = 5
    ): Collection {
        return $this->completedProductSales($from, $to)
            ->select('products.id', 'products.name')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->limit(max(1, min($limit, 20)))
            ->get();
    }

    private function validGrouping(string $groupBy): string
    {
        return in_array($groupBy, ['day', 'week', 'month'], true) ? $groupBy : 'day';
    }

    /**
     * @return list<string>
     */
    private function trendLabels(CarbonImmutable $from, CarbonImmutable $to, string $groupBy): array
    {
        $labels = [];
        $date = match ($groupBy) {
            'week' => $from->startOfWeek(),
            'month' => $from->startOfMonth(),
            default => $from->startOfDay(),
        };

        while ($date <= $to) {
            $labels[] = $this->trendBucket($date, $groupBy);
            $date = match ($groupBy) {
                'week' => $date->addWeek(),
                'month' => $date->addMonth(),
                default => $date->addDay(),
            };
        }

        return $labels;
    }

    private function trendBucket(CarbonImmutable $date, string $groupBy): string
    {
        return match ($groupBy) {
            'week' => $date->startOfWeek()->toDateString(),
            'month' => $date->format('Y-m'),
            default => $date->toDateString(),
        };
    }
}
