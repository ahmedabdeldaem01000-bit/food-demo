<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2).' '.strtoupper($record->currency))
                    ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed', 'delivered' => 'success',
                        'pending' => 'warning',
                        'failed', 'cancelled', 'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                // ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'canceled' => 'Canceled (legacy)',
                        'failed' => 'Failed',
                        'delivered' => 'Delivered',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
