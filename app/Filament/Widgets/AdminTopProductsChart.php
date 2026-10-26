<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class AdminTopProductsChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Top products · last 30 days';

    protected ?string $description = 'Completed-order quantities. Item prices have no currency column, so amounts are intentionally omitted.';

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:OperationalDashboard') ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return ['indexAxis' => 'y'];
    }

    protected function getData(): array
    {
        $products = app(AdminReportService::class)->topProducts(
            CarbonImmutable::now()->subDays(29)->startOfDay(),
            CarbonImmutable::now(),
        );

        return [
            'labels' => $products->pluck('name')->all(),
            'datasets' => [[
                'label' => 'Units sold',
                'data' => $products->pluck('units_sold')->map(fn ($count): int => (int) $count)->all(),
            ]],
        ];
    }
}
