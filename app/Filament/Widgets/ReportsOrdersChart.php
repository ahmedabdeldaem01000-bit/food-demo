<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;

class ReportsOrdersChart extends ReportChartWidget
{
    protected ?string $heading = 'Orders over time';

    protected ?string $description = 'Order creation counts for the selected date range.';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->reportRange();

        return app(AdminReportService::class)->ordersTrend(
            $from,
            $to,
            $this->groupBy,
            $this->orderStatus ?: null,
            $this->discountKey(),
            ctype_digit($this->categoryId) ? (int) $this->categoryId : null,
            $this->paymentStatus ?: null,
            $this->paymentMethod ?: null,
        );
    }
}
