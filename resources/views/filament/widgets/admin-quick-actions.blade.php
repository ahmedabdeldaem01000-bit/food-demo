<x-filament-widgets::widget>
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Quick actions</h2>
        <nav class="mt-4 flex flex-wrap gap-3" aria-label="Admin quick actions">
            @can('ViewAny:Order')
                <a class="fi-btn fi-btn-size-md fi-btn-color-gray" href="{{ url('/admin/orders') }}">View orders</a>
            @endcan
            @can('Create:Product')
                <a class="fi-btn fi-btn-size-md fi-btn-color-primary" href="{{ url('/admin/products/create') }}">Create product</a>
            @endcan
            @can('ViewAny:Payment')
                <a class="fi-btn fi-btn-size-md fi-btn-color-gray" href="{{ url('/admin/payments') }}">View payments</a>
            @endcan
            @can('viewAny', \App\Models\Discount::class)
                <a class="fi-btn fi-btn-size-md fi-btn-color-gray" href="{{ url('/admin/discounts') }}">Manage discounts</a>
            @endcan
            @can('View:AdminReports')
                <a class="fi-btn fi-btn-size-md fi-btn-color-gray" href="{{ url('/admin/reports') }}">View reports</a>
            @endcan
        </nav>
    </div>
</x-filament-widgets::widget>
