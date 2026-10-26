<x-filament-widgets::widget>
    <x-filament::section heading="Operational alerts" description="Items that may need administrator attention.">
        <div class="space-y-3">
            @if ($alerts['expiring_discounts'] > 0)
                <a href="{{ url('/admin/discounts') }}" class="flex items-center justify-between gap-4 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm font-medium text-warning-800 transition hover:bg-warning-100 dark:border-warning-400/20 dark:bg-warning-400/10 dark:text-warning-200">
                    <span>{{ number_format($alerts['expiring_discounts']) }} active coupon(s) expire within 7 days</span>
                    <span aria-hidden="true">View coupons →</span>
                </a>
            @endif

            @if ($alerts['completed_orders_without_settled_payment'] > 0)
                <a href="{{ url('/admin/orders?tableFilters[status][value]=completed') }}" class="flex items-center justify-between gap-4 rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm font-medium text-danger-800 transition hover:bg-danger-100 dark:border-danger-400/20 dark:bg-danger-400/10 dark:text-danger-200">
                    <span>{{ number_format($alerts['completed_orders_without_settled_payment']) }} completed order(s) have no settled payment record</span>
                    <span aria-hidden="true">Review orders →</span>
                </a>
            @endif

            @if ($alerts['expiring_discounts'] === 0 && $alerts['completed_orders_without_settled_payment'] === 0)
                <p class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                    No data quality or coupon expiry alerts.
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
