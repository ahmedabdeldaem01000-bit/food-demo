<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;

class ReportsOrderStatusChart extends ReportChartWidget
{
    protected ?string $heading = 'Order status distribution';

    protected ?string $description = 'Orders in the selected period.';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->reportRange();
        $rows = app(AdminReportService::class)->orderStatusDistribution(
            $from,
            $to,
            $this->orderStatus ?: null,
            $this->discountKey(),
            ctype_digit($this->categoryId) ? (int) $this->categoryId : null,
            $this->paymentStatus ?: null,
            $this->paymentMethod ?: null,
        );

        return [
            'labels' => $rows->pluck('status')->all(),
            'datasets' => [[
                'label' => 'Orders',
                'data' => $rows->pluck('total')->map(fn ($count): int => (int) $count)->all(),
            ]],
        ];
    }
}
