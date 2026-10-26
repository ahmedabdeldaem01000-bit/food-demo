<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class AdminSalesTrendChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Collected sales';

    protected ?string $description = 'Completed payments per day, shown as separate currency series.';

    public ?string $filter = '30';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminDashboard') ?? false;
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

        return app(AdminReportService::class)->revenueTrend($from, $to, $groupBy);
    }
}
