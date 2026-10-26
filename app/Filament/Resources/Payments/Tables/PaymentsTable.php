<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Payment')
                    ->sortable(),
                TextColumn::make('order_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('method')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state, $record): string => number_format((float) $state, 2).' '.strtoupper($record->currency))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'completed', 'paid' => 'success',
                        'pending' => 'warning',
                        'failed', 'cancelled', 'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paypal_capture_id')
                    ->label('PayPal capture')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(fn (): array => Payment::query()
                        ->distinct()
                        ->orderBy('status')
                        ->pluck('status', 'status')
                        ->all()),
                SelectFilter::make('method')
                    ->options(fn (): array => Payment::query()
                        ->distinct()
                        ->orderBy('method')
                        ->pluck('method', 'method')
                        ->all()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
