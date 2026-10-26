<?php

namespace App\Filament\Resources\Discounts\Schemas;

use App\Models\Discount;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Offer')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('code')->copyable(),
                    TextEntry::make('description')->placeholder('No description'),
                    TextEntry::make('type')
                        ->formatStateUsing(fn (string $state): string => $state === 'percentage' ? 'Percentage' : 'Fixed amount'),
                    TextEntry::make('value')
                        ->formatStateUsing(fn ($state, Discount $record): string => number_format((float) $state, 2).($record->type === 'percentage' ? '%' : ' EGP')),
                    TextEntry::make('minimum_order_amount')
                        ->money('EGP'),
                    TextEntry::make('status')
                        ->state(fn (Discount $record): string => $record->statusLabel())
                        ->badge(),
                ])
                ->columns(2),
            Section::make('Availability and usage')
                ->schema([
                    TextEntry::make('starts_at')->dateTime()->placeholder('Immediately'),
                    TextEntry::make('ends_at')->dateTime()->placeholder('No end date'),
                    TextEntry::make('usage_count')
                        ->label('Usage')
                        ->formatStateUsing(fn ($state, Discount $record): string => $record->usage_limit === null
                            ? (string) $state.' / Unlimited'
                            : $state.' / '.$record->usage_limit),
                    TextEntry::make('created_at')->dateTime(),
                ])
                ->columns(2),
        ]);
    }
}
