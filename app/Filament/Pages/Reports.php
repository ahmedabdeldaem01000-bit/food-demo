<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Admin\AdminReportService;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use UnitEnum;

class Reports extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    #[Url(as: 'from', except: '')]
    public string $fromDate = '';

    #[Url(as: 'to', except: '')]
    public string $toDate = '';

    #[Url(as: 'order_status', except: '')]
    public string $orderStatus = '';

    #[Url(as: 'payment_status', except: '')]
    public string $paymentStatus = '';

    #[Url(as: 'payment_method', except: '')]
    public string $paymentMethod = '';

    #[Url(as: 'category', except: '')]
    public string $categoryId = '';

    #[Url(as: 'coupon', except: '')]
    public string $discountId = '';

    #[Url(as: 'group', except: 'day')]
    public string $groupBy = 'day';

    public function mount(): void
    {
        $defaultFrom = now()->subDays(29)->toDateString();
        $defaultTo = now()->toDateString();
        $from = $this->parseDate($this->fromDate);
        $to = $this->parseDate($this->toDate);

        if (! $from || ! $to || $from->greaterThan($to) || $from->diffInDays($to) > 365) {
            $from = CarbonImmutable::parse($defaultFrom);
            $to = CarbonImmutable::parse($defaultTo);
        }

        $this->fromDate = $from->toDateString();
        $this->toDate = $to->toDateString();
        $this->orderStatus = $this->validOption($this->orderStatus, Order::query()->distinct()->pluck('status')->all());
        $this->paymentStatus = $this->validOption($this->paymentStatus, $this->paymentStatuses());
        $this->paymentMethod = $this->validOption($this->paymentMethod, $this->paymentMethods());
        $this->categoryId = $this->validId($this->categoryId, Category::class);
        $this->discountId = $this->validId($this->discountId, Discount::class);
        $this->groupBy = in_array($this->groupBy, ['day', 'week', 'month'], true) ? $this->groupBy : 'day';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:AdminReports') ?? false;
    }

    public function applyFilters(): void
    {
        Validator::make($this->only([
            'fromDate',
            'toDate',
            'orderStatus',
            'paymentStatus',
            'paymentMethod',
            'categoryId',
            'discountId',
            'groupBy',
        ]), [
            'fromDate' => ['required', 'date_format:Y-m-d'],
            'toDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:fromDate'],
            'orderStatus' => ['nullable', 'string', 'in:'.implode(',', Order::query()->distinct()->pluck('status')->all())],
            'paymentStatus' => ['nullable', 'string', 'in:'.implode(',', $this->paymentStatuses())],
            'paymentMethod' => ['nullable', 'string', 'in:'.implode(',', $this->paymentMethods())],
            'categoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'discountId' => ['nullable', 'integer', 'exists:discounts,id'],
            'groupBy' => ['required', 'in:day,week,month'],
        ])->validate();

        $from = $this->parseDate($this->fromDate);
        $to = $this->parseDate($this->toDate);

        if (! $from || ! $to || $from->diffInDays($to) > 365) {
            throw ValidationException::withMessages([
                'toDate' => 'Choose a date range of no more than 366 days.',
            ]);
        }
    }

    public function resetFilters(): void
    {
        $this->fromDate = now()->subDays(29)->toDateString();
        $this->toDate = now()->toDateString();
        $this->orderStatus = '';
        $this->paymentStatus = '';
        $this->paymentMethod = '';
        $this->categoryId = '';
        $this->discountId = '';
        $this->groupBy = 'day';
        $this->resetValidation();
    }

    public function exportOrders()
    {
        abort_unless(static::canAccess(), 403);
        $this->applyFilters();

        $from = $this->parseDate($this->fromDate)?->startOfDay();
        $to = $this->parseDate($this->toDate)?->endOfDay();
        abort_unless($from && $to, 422);

        $orders = app(AdminReportService::class)->ordersForReport(
            $from,
            $to,
            $this->orderStatus ?: null,
            $this->paymentStatus ?: null,
            $this->paymentMethod ?: null,
            $this->integerId($this->discountId),
            $this->integerId($this->categoryId),
        );

        return response()->streamDownload(function () use ($orders): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Order ID', 'Created at', 'Order status', 'Order amount', 'Currency', 'Coupon', 'Discount amount', 'Latest payment status']);

            $orders->chunkById(500, function ($batch) use ($stream): void {
                foreach ($batch as $order) {
                    fputcsv($stream, [
                        $order->getKey(),
                        $order->created_at?->toIso8601String(),
                        $order->status,
                        $order->amount,
                        strtoupper((string) $order->currency),
                        $order->discount?->code,
                        $order->discount_amount,
                        $order->latestPayment?->status,
                    ]);
                }
            });

            fclose($stream);
        }, 'foodify-orders-'.$this->fromDate.'-to-'.$this->toDate.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function getViewData(): array
    {
        $from = $this->parseDate($this->fromDate) ?? now()->subDays(29)->toImmutable();
        $to = $this->parseDate($this->toDate) ?? now()->toImmutable();
        $from = $from->startOfDay();
        $to = $to->endOfDay();

        return [
            'report' => app(AdminReportService::class)->report(
                $from,
                $to,
                $this->orderStatus ?: null,
                $this->paymentStatus ?: null,
                $this->paymentMethod ?: null,
                $this->integerId($this->categoryId),
                $this->integerId($this->discountId),
                $this->groupBy,
            ),
            'fromDate' => $from->toDateString(),
            'toDate' => $to->toDateString(),
        ];
    }

    private function parseDate(string $date): ?CarbonImmutable
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

    /**
     * @param  list<string>  $options
     */
    private function validOption(string $value, array $options): string
    {
        return in_array($value, $options, true) ? $value : '';
    }

    /**
     * @return list<string>
     */
    private function paymentStatuses(): array
    {
        return Payment::query()
            ->selectRaw('LOWER(status) as status')
            ->groupByRaw('LOWER(status)')
            ->orderBy('status')
            ->pluck('status')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function paymentMethods(): array
    {
        return Payment::query()
            ->selectRaw('LOWER(method) as method')
            ->groupByRaw('LOWER(method)')
            ->orderBy('method')
            ->pluck('method')
            ->all();
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function validId(string $value, string $model): string
    {
        $id = $this->integerId($value);

        return $id && $model::query()->whereKey($id)->exists() ? (string) $id : '';
    }

    private function integerId(string $value): ?int
    {
        return ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }
}
