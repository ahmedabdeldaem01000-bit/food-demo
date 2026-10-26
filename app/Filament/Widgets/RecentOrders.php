<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrders extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('ViewAny:Order') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user:id,name', 'latestPayment'])
                    ->latest('created_at')
            )
            ->heading('Recent orders')
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->placeholder('Guest'),
                TextColumn::make('amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state, Order $record) => number_format((float) $state, 2).' '.strtoupper($record->currency))
                    ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed', 'delivered' => 'success',
                        'pending' => 'warning',
                        'failed', 'cancelled', 'canceled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('latestPayment.status')
                    ->label('Payment')
                    ->badge()
                    ->placeholder('No payment')
                    ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10]);
    }
}
