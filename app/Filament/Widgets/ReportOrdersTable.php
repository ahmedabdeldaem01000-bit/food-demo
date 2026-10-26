<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Services\Admin\AdminReportService;
use Carbon\CarbonImmutable;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ReportOrdersTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public string $fromDate;

    public string $toDate;

    public string $orderStatus = '';

    public string $paymentStatus = '';

    public string $paymentMethod = '';

    public string $categoryId = '';

    public string $discountId = '';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminReports') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => app(AdminReportService::class)->ordersForReport(
                CarbonImmutable::parse($this->fromDate)->startOfDay(),
                CarbonImmutable::parse($this->toDate)->endOfDay(),
                $this->orderStatus ?: null,
                $this->paymentStatus ?: null,
                $this->paymentMethod ?: null,
                ctype_digit($this->discountId) && (int) $this->discountId > 0 ? (int) $this->discountId : null,
                ctype_digit($this->categoryId) && (int) $this->categoryId > 0 ? (int) $this->categoryId : null,
            ))
            ->heading('Orders in selected period')
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('Guest'),
                TextColumn::make('amount')
                    ->label('Order total')
                    ->formatStateUsing(fn ($state, Order $record): string => number_format((float) $state, 2).' '.strtoupper($record->currency))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('latestPayment.status')
                    ->label('Latest payment')
                    ->badge()
                    ->placeholder('No payment'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50]);
    }
}
