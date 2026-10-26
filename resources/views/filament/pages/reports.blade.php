<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Business performance</x-slot>
            <x-slot name="description">Collected payment revenue, order activity, customer trends, products, and coupon usage. Revenue is reported separately for each recorded currency.</x-slot>

            <form wire:submit="applyFilters" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>From</span>
                        <input type="date" wire:model="fromDate" class="fi-input rounded-lg">
                        @error('fromDate') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>To</span>
                        <input type="date" wire:model="toDate" class="fi-input rounded-lg">
                        @error('toDate') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>Order status</span>
                        <select wire:model="orderStatus" class="fi-input rounded-lg">
                            <option value="">All order statuses</option>
                            @foreach ($report['order_status_options'] as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('orderStatus') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>Payment status</span>
                        <select wire:model="paymentStatus" class="fi-input rounded-lg">
                            <option value="">All payment statuses</option>
                            @foreach ($report['payment_status_options'] as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('paymentStatus') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>Payment method</span>
                        <select wire:model="paymentMethod" class="fi-input rounded-lg">
                            <option value="">All payment methods</option>
                            @foreach ($report['payment_method_options'] as $method)
                                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                        @error('paymentMethod') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>Category</span>
                        <select wire:model="categoryId" class="fi-input rounded-lg">
                            <option value="">All categories</option>
                            @foreach ($report['category_options'] as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('categoryId') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>Coupon</span>
                        <select wire:model="discountId" class="fi-input rounded-lg">
                            <option value="">All coupons</option>
                            @foreach ($report['discount_options'] as $id => $code)
                                <option value="{{ $id }}">{{ $code }}</option>
                            @endforeach
                        </select>
                        @error('discountId') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">
                        <span>Chart interval</span>
                        <select wire:model="groupBy" class="fi-input rounded-lg">
                            <option value="day">Daily</option>
                            <option value="week">Weekly</option>
                            <option value="month">Monthly</option>
                        </select>
                        @error('groupBy') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-white/10">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ $fromDate }} through {{ $toDate }}. Default: the latest 30 calendar days, including today. Maximum: 366 days.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <x-filament::button type="button" color="gray" wire:click="resetFilters">Reset</x-filament::button>
                        <x-filament::button type="submit" icon="heroicon-m-funnel">Apply filters</x-filament::button>
                        <x-filament::button type="button" color="gray" icon="heroicon-m-arrow-down-tray" wire:click="exportOrders">
                            Export orders CSV
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </x-filament::section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Report summary">
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Orders</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($report['orders_total']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Previous period: {{ number_format($report['previous_orders_total']) }}</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed orders</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($report['orders_completed']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Of {{ number_format($report['orders_in_range_count']) }} matching orders</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Collected revenue</p>
                @forelse ($report['revenue_by_currency'] as $revenue)
                    <p class="mt-2 text-lg font-semibold">{{ number_format((float) $revenue->total, 2) }} {{ $revenue->currency }}</p>
                    <p class="text-xs text-gray-500">
                        Previous period: {{ number_format((float) ($report['previous_revenue_by_currency']->firstWhere('currency', $revenue->currency)?->total ?? 0), 2) }} {{ $revenue->currency }}
                    </p>
                @empty
                    <p class="mt-2 text-lg font-semibold">No settled payments</p>
                @endforelse
                <p class="mt-1 text-xs text-gray-500">One latest settled payment per order</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment success rate</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($report['payment_success_rate'], 1) }}%</p>
                <p class="mt-1 text-xs text-gray-500">{{ number_format($report['successful_payment_orders']) }} of {{ number_format($report['payment_orders_attempted']) }} orders with payment attempts</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average order value</p>
                @forelse ($report['average_order_value_by_currency'] as $average)
                    <p class="mt-2 text-lg font-semibold">{{ number_format((float) $average->average_amount, 2) }} {{ $average->currency }}</p>
                @empty
                    <p class="mt-2 text-lg font-semibold">No settled orders</p>
                @endforelse
                <p class="mt-1 text-xs text-gray-500">Based on settled payment amounts</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cancellation rate</p>
                <p class="mt-2 text-2xl font-semibold">{{ $report['orders_in_range_count'] === 0 ? '0.0' : number_format($report['orders_cancelled'] / $report['orders_in_range_count'] * 100, 1) }}%</p>
                <p class="mt-1 text-xs text-gray-500">{{ number_format($report['orders_cancelled']) }} cancelled orders</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Discount value applied</p>
                @php($discountTotals = $report['discount_totals_by_currency']->flatten(1)->groupBy('currency'))
                @forelse ($discountTotals as $currency => $totals)
                    <p class="mt-2 text-lg font-semibold">{{ number_format((float) $totals->sum('discount_total'), 2) }} {{ $currency }}</p>
                @empty
                    <p class="mt-2 text-lg font-semibold">0.00</p>
                @endforelse
                <p class="mt-1 text-xs text-gray-500">Completed orders only</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer activity</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($report['customers_with_orders']) }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ number_format($report['repeat_customers']) }} repeat customers in the period</p>
            </x-filament::section>
        </section>

        <section class="grid gap-6 xl:grid-cols-2" aria-label="Trends and distributions">
            @livewire(\App\Filament\Widgets\ReportsRevenueChart::class, ['fromDate' => $fromDate, 'toDate' => $toDate, 'groupBy' => $this->groupBy, 'paymentStatus' => $this->paymentStatus, 'paymentMethod' => $this->paymentMethod, 'categoryId' => $this->categoryId, 'discountId' => $this->discountId], 'revenue-'.$fromDate.'-'.$toDate.'-'.$this->groupBy.'-'.$this->paymentStatus.'-'.$this->paymentMethod.'-'.$this->categoryId.'-'.$this->discountId)
            @livewire(\App\Filament\Widgets\ReportsOrdersChart::class, ['fromDate' => $fromDate, 'toDate' => $toDate, 'groupBy' => $this->groupBy, 'orderStatus' => $this->orderStatus, 'paymentStatus' => $this->paymentStatus, 'paymentMethod' => $this->paymentMethod, 'categoryId' => $this->categoryId, 'discountId' => $this->discountId], 'orders-'.$fromDate.'-'.$toDate.'-'.$this->groupBy.'-'.$this->orderStatus.'-'.$this->paymentStatus.'-'.$this->paymentMethod.'-'.$this->categoryId.'-'.$this->discountId)
            @livewire(\App\Filament\Widgets\ReportsOrderStatusChart::class, ['fromDate' => $fromDate, 'toDate' => $toDate, 'orderStatus' => $this->orderStatus, 'paymentStatus' => $this->paymentStatus, 'paymentMethod' => $this->paymentMethod, 'categoryId' => $this->categoryId, 'discountId' => $this->discountId], 'order-status-'.$fromDate.'-'.$toDate.'-'.$this->orderStatus.'-'.$this->paymentStatus.'-'.$this->paymentMethod.'-'.$this->categoryId.'-'.$this->discountId)
            @livewire(\App\Filament\Widgets\ReportsPaymentStatusChart::class, ['fromDate' => $fromDate, 'toDate' => $toDate, 'paymentStatus' => $this->paymentStatus, 'paymentMethod' => $this->paymentMethod, 'categoryId' => $this->categoryId, 'discountId' => $this->discountId], 'payment-status-'.$fromDate.'-'.$toDate.'-'.$this->paymentStatus.'-'.$this->paymentMethod.'-'.$this->categoryId.'-'.$this->discountId)
        </section>

        <section class="grid gap-6 xl:grid-cols-2" aria-label="Sales and payment details">
            <x-filament::section heading="Collected revenue by currency and method" description="Successful records only; payment attempts are deduplicated by order.">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead><tr class="border-b border-gray-200 text-gray-500 dark:border-white/10"><th class="py-3 pr-4">Method</th><th class="py-3 pr-4">Currency</th><th class="py-3 text-right">Collected</th></tr></thead>
                        <tbody>
                            @forelse ($report['revenue_by_method'] as $row)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5"><td class="py-3 pr-4">{{ ucfirst($row->method) }}</td><td class="py-3 pr-4">{{ $row->currency }}</td><td class="py-3 text-right font-medium">{{ number_format((float) $row->collected, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-gray-500">No settled payments for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section heading="Payment attempts" description="Attempt counts and recorded amounts are grouped by status, method, and currency.">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead><tr class="border-b border-gray-200 text-gray-500 dark:border-white/10"><th class="py-3 pr-3">Status</th><th class="py-3 pr-3">Method</th><th class="py-3 pr-3">Currency</th><th class="py-3 pr-3 text-right">Attempts</th><th class="py-3 text-right">Recorded</th></tr></thead>
                        <tbody>
                            @forelse ($report['payment_statuses'] as $payment)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5"><td class="py-3 pr-3"><x-filament::badge :color="in_array($payment->status, ['paid', 'completed'], true) ? 'success' : ($payment->status === 'pending' ? 'warning' : 'gray')">{{ ucfirst($payment->status) }}</x-filament::badge></td><td class="py-3 pr-3">{{ ucfirst($payment->method) }}</td><td class="py-3 pr-3">{{ $payment->currency }}</td><td class="py-3 pr-3 text-right">{{ number_format($payment->payment_count) }}</td><td class="py-3 text-right">{{ number_format((float) $payment->recorded_amount, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-gray-500">No payment attempts for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </section>

        <section class="grid gap-6 xl:grid-cols-2" aria-label="Product and customer insights">
            <x-filament::section heading="Best-selling products" description="Units from completed orders. Item currency is not recorded, so monetary product totals are omitted.">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead><tr class="border-b border-gray-200 text-gray-500 dark:border-white/10"><th class="py-3 pr-4">Product</th><th class="py-3 text-right">Units</th></tr></thead>
                        <tbody>
                            @forelse ($report['top_products'] as $product)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5"><td class="py-3 pr-4"><a class="text-primary-600 hover:underline" href="{{ url('/admin/products/'.$product->id.'/edit') }}">{{ $product->name }}</a></td><td class="py-3 text-right font-medium">{{ number_format($product->units_sold) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="py-6 text-center text-gray-500">No completed product sales in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section heading="Sales by category" description="Units sold; no currency is inferred from item prices.">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead><tr class="border-b border-gray-200 text-gray-500 dark:border-white/10"><th class="py-3 pr-4">Category</th><th class="py-3 text-right">Units</th></tr></thead>
                        <tbody>
                            @forelse ($report['category_sales'] as $category)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5"><td class="py-3 pr-4"><a class="text-primary-600 hover:underline" href="{{ url('/admin/categories/'.$category->id.'/edit') }}">{{ $category->name }}</a></td><td class="py-3 text-right font-medium">{{ number_format($category->units_sold) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="py-6 text-center text-gray-500">No completed category sales in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section heading="Products with no completed sales" description="Products with no matching completed order items in the selected period.">
                <div class="space-y-3">
                    @forelse ($report['products_without_sales'] as $product)
                        <a class="block border-b border-gray-100 pb-3 text-sm text-primary-600 last:border-0 last:pb-0 hover:underline dark:border-white/5" href="{{ url('/admin/products/'.$product->id.'/edit') }}">
                            {{ $product->name }}
                        </a>
                    @empty
                        <p class="py-4 text-center text-sm text-gray-500">No unsold products in the selected report scope.</p>
                    @endforelse
                </div>
            </x-filament::section>

            <x-filament::section heading="Top customers" description="Ranked by orders placed in the selected period.">
                <div class="space-y-3">
                    @forelse ($report['top_customers_by_orders'] as $customer)
                        <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3 last:border-0 last:pb-0 dark:border-white/5">
                            <span class="font-medium">{{ $customer->name }}</span>
                            <x-filament::badge>{{ number_format($customer->orders_in_period_count) }} orders</x-filament::badge>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-gray-500">No customers placed orders in this period.</p>
                    @endforelse
                </div>
            </x-filament::section>

            <x-filament::section heading="Coupon performance" description="Completed orders and applied discounts by recorded currency. Lifetime use counts are not treated as customer-level usage.">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead><tr class="border-b border-gray-200 text-gray-500 dark:border-white/10"><th class="py-3 pr-3">Coupon</th><th class="py-3 pr-3 text-right">Orders</th><th class="py-3 text-right">Lifetime use / limit</th></tr></thead>
                        <tbody>
                            @forelse ($report['discount_usage'] as $discount)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5"><td class="py-3 pr-3"><a class="text-primary-600 hover:underline" href="{{ url('/admin/discounts/'.$discount->id.'/edit') }}">{{ $discount->code }}</a></td><td class="py-3 pr-3 text-right">{{ number_format($discount->successful_orders_count) }}</td><td class="py-3 text-right">{{ number_format($discount->usage_count) }} / {{ $discount->usage_limit ?? 'Unlimited' }}</td></tr>
                                @foreach ($report['discount_totals_by_currency']->get($discount->id, collect()) as $total)
                                    <tr class="text-xs text-gray-500"><td colspan="3" class="pb-2 pl-4">{{ number_format((float) $total->discount_total, 2) }} {{ $total->currency }} discount value</td></tr>
                                @endforeach
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-gray-500">No coupon use in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-sm text-gray-500">Active {{ number_format($report['active_discounts']) }} · Scheduled {{ number_format($report['scheduled_discounts']) }} · Expired {{ number_format($report['expired_discounts']) }} · Expiring within 7 days {{ number_format($report['expiring_discounts']) }}</p>
            </x-filament::section>
        </section>

        @livewire(\App\Filament\Widgets\ReportOrdersTable::class, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'orderStatus' => $this->orderStatus,
            'paymentStatus' => $this->paymentStatus,
            'paymentMethod' => $this->paymentMethod,
            'categoryId' => $this->categoryId,
            'discountId' => $this->discountId,
        ], 'report-orders-'.$fromDate.'-'.$toDate.'-'.$this->orderStatus.'-'.$this->paymentStatus.'-'.$this->paymentMethod.'-'.$this->categoryId.'-'.$this->discountId)

        <x-filament::section>
            <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
                Revenue includes only the latest settled payment per order (legacy <code>paid</code> and current <code>completed</code> statuses). Pending and failed attempts are excluded. Currency totals are never combined. Payment success rate is based on unique orders with attempts in the selected period. Product and category reports use completed-order quantities because order items do not store a reliable currency or cost. Discount totals come from completed orders; refunds and currency conversion are not represented by the current schema.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
