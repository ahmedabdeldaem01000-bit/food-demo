<?php

namespace App\Filament\Widgets;

use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

abstract class ReportChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public string $fromDate = '';

    public string $toDate = '';

    public string $orderStatus = '';

    public string $paymentStatus = '';

    public string $paymentMethod = '';

    public string $categoryId = '';

    public string $discountId = '';

    public string $groupBy = 'day';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminReports') ?? false;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function reportRange(): array
    {
        $from = $this->validDate($this->fromDate);
        $to = $this->validDate($this->toDate);

        abort_unless($from && $to && $from->lessThanOrEqualTo($to) && $from->diffInDays($to) <= 365, 422);

        return [$from->startOfDay(), $to->endOfDay()];
    }

    protected function discountKey(): ?int
    {
        return ctype_digit($this->discountId) && (int) $this->discountId > 0
            ? (int) $this->discountId
            : null;
    }

    private function validDate(string $date): ?CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $parsed && $parsed->format('Y-m-d') === $date ? $parsed : null;
    }
}
