<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Operations';

    protected ?string $description = 'Order and catalog activity. Financial metrics are restricted to administrators.';

    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->can('View:OperationalDashboard') ?? false)
            && ! ($user?->can('View:AdminDashboard') ?? false);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Orders today', Order::query()->whereDate('created_at', today())->count())
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url('/admin/orders'),
            Stat::make('Pending orders', Order::query()->where('status', 'pending')->count())
                ->color('warning')
                ->url('/admin/orders?tableFilters[status][value]=pending'),
            Stat::make('Products', Product::query()->count())
                ->url('/admin/products'),
        ];
    }
}
