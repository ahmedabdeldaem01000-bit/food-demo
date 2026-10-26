<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Executive overview';

    protected ?string $description = 'Seven-day performance compared with the previous seven days. Settled revenue is separated by currency.';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminDashboard') ?? false;
    }

    protected function getStats(): array
    {
        $metrics = app(AdminReportService::class)->dashboard();
        $stats = [];

        foreach ($metrics['revenue_comparison_7d'] as $revenue) {
            $comparison = $revenue['change_percent'] === null
                ? 'No prior-period baseline'
                : sprintf('%+.1f%% vs previous 7 days', $revenue['change_percent']);

            $stats[] = Stat::make(
                'Collected · '.$revenue['currency'],
                number_format($revenue['total'], 2).' '.$revenue['currency']
            )
                ->description($comparison)
                ->icon(Heroicon::CurrencyDollar)
                ->color('success')
                ->url('/admin/reports');
        }

        if ($stats === []) {
            $stats[] = Stat::make('Collected revenue · last 7 days', 'No settled payments')
                ->description('Settled revenue will appear here by currency.')
                ->icon(Heroicon::CurrencyDollar)
                ->url('/admin/reports');
        }

        $stats[] = Stat::make('Orders · 7 days', number_format($metrics['orders_7d']))
            ->description($metrics['order_change_percent_7d'] === null
                ? 'No prior-period baseline'
                : sprintf('%+.1f%% vs previous 7 days', $metrics['order_change_percent_7d']))
            ->icon(Heroicon::OutlinedRectangleStack)
            ->url('/admin/orders');

        $stats[] = Stat::make('Completed orders · 7 days', number_format($metrics['completed_orders_7d']))
            ->description('Orders marked completed')
            ->color('success')
            ->url('/admin/orders?tableFilters[status][value]=completed');

        $stats[] = Stat::make('Payment success rate · 7 days', number_format($metrics['payment_success_rate_7d'], 1).'%')
            ->description(number_format($metrics['successful_payment_orders_7d']).' settled orders / '.number_format($metrics['payment_orders_attempted_7d']).' attempted orders')
            ->url('/admin/payments');

        $stats[] = Stat::make('Cancelled · 7 days', number_format($metrics['cancelled_orders_7d']))
            ->description(number_format($metrics['cancel_rate_7d'], 1).'% of orders in this period')
            ->color($metrics['cancelled_orders_7d'] > 0 ? 'warning' : 'success')
            ->url('/admin/orders?tableFilters[status][value]=cancelled');

        return $stats;
    }
}
