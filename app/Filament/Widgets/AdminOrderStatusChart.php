<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class AdminOrderStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Order status · last 7 days';

    protected ?string $description = 'Counts are based on order creation date, regardless of payment status.';

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:OperationalDashboard') ?? false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $to = CarbonImmutable::now();
        $from = $to->subDays(6)->startOfDay();
        $statuses = app(AdminReportService::class)->orderStatusDistribution($from, $to);

        return [
            'labels' => $statuses->pluck('status')->all(),
            'datasets' => [[
                'label' => 'Orders',
                'data' => $statuses->pluck('total')->map(fn ($count): int => (int) $count)->all(),
            ]],
        ];
    }
}
