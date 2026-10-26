<?php

namespace App\Filament\Resources\Discounts\Tables;

use App\Models\Discount;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class DiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'percentage' ? 'Percentage' : 'Fixed amount')
                    ->color('gray'),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state, Discount $record): string => number_format((float) $state, 2).($record->type === 'percentage' ? '%' : ' EGP'))
                    ->sortable(),
                TextColumn::make('status')
                    ->state(fn (Discount $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Scheduled' => 'warning',
                        'Expired' => 'danger',
                        'Limit reached' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('usage_count')
                    ->label('Uses')
                    ->formatStateUsing(fn ($state, Discount $record): string => $record->usage_limit === null
                        ? (string) $state.' / Unlimited'
                        : $state.' / '.$record->usage_limit)
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Immediately'),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No end date'),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Activation')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('activate')
                    ->label('Activate')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Discount $record): bool => ! $record->is_active)
                    ->action(function (Discount $record): void {
                        Gate::authorize('update', $record);
                        $record->update(['is_active' => true]);
                    }),
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Discount $record): bool => $record->is_active)
                    ->action(function (Discount $record): void {
                        Gate::authorize('update', $record);
                        $record->update(['is_active' => false]);
                    }),
                DeleteAction::make()
                    ->visible(fn (Discount $record): bool => ! $record->orders()->exists()),
            ]);
    }
}
