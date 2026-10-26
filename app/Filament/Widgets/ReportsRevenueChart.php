<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;

class ReportsRevenueChart extends ReportChartWidget
{
    protected ?string $heading = 'Collected revenue by currency';

    protected ?string $description = 'One latest settled payment per order. Currency series are never combined.';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->reportRange();

        return app(AdminReportService::class)->revenueTrend(
            $from,
            $to,
            $this->groupBy,
            $this->paymentMethod ?: null,
            $this->discountKey(),
            $this->paymentStatus ?: null,
            ctype_digit($this->categoryId) ? (int) $this->categoryId : null,
        );
    }
}
