<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class OrdersTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Orders over time';

    protected ?string $description = 'Order creation counts, independent of payment status.';

    public ?string $filter = '30';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:OperationalDashboard') ?? false;
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 days · daily',
            '30' => '30 days · daily',
            '90' => '90 days · weekly',
            '365' => '12 months · monthly',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = in_array($this->filter, ['7', '30', '90', '365'], true) ? (int) $this->filter : 30;
        $to = CarbonImmutable::now();
        $from = $to->subDays($days - 1)->startOfDay();
        $groupBy = $days >= 365 ? 'month' : ($days >= 90 ? 'week' : 'day');

        return app(AdminReportService::class)->ordersTrend($from, $to, $groupBy);
    }
}
