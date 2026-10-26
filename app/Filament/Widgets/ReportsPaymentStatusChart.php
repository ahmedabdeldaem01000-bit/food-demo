<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;

class ReportsPaymentStatusChart extends ReportChartWidget
{
    protected ?string $heading = 'Payment attempts by status';

    protected ?string $description = 'Attempt records, not unique orders. Success rate counts unique orders separately.';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->reportRange();
        $rows = app(AdminReportService::class)->paymentStatusDistribution(
            $from,
            $to,
            $this->paymentStatus ?: null,
            $this->paymentMethod ?: null,
            $this->discountKey(),
            ctype_digit($this->categoryId) ? (int) $this->categoryId : null,
        );

        return [
            'labels' => $rows->pluck('status')->all(),
            'datasets' => [[
                'label' => 'Payment attempts',
                'data' => $rows->pluck('total')->map(fn ($count): int => (int) $count)->all(),
            ]],
        ];
    }
}
