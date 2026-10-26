<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
// use Filament\Infolists\Components\Section;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // 🧾 معلومات أساسية
                Section::make('Order Info')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('id')
                                ->label('Order ID')
                                ->badge()
                                ->color('gray'),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    default => 'gray',
                                }),

                            TextEntry::make('amount')
                                ->label('Total')
                                ->formatStateUsing(
                                    fn ($state, $record) => number_format($state, 2).' '.$record->currency
                                )
                                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
                        ]),
                    ])
                    ->columns(1),

                // 👤 المستخدم
                Section::make('Customer')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('user.name')
                                ->label('Name')
                                ->placeholder('-'),

                            TextEntry::make('user.email')
                                ->label('Email')
                                ->placeholder('-')
                                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
                        ]),
                    ]),

                // 💳 الدفع
                Section::make('Payment Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('paypal_order_id')
                                ->label('PayPal ID')
                                ->copyable()
                                ->placeholder('-')
                                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),

                        ]),
                    ]),

                // ⏱ التواريخ
                Section::make('Timestamps')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime(),

                            TextEntry::make('updated_at')
                                ->label('Last Update')
                                ->dateTime(),
                        ]),
                    ]),

                Section::make('Items')
                    ->schema([Grid::make(3)->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product.name')->label('Product Name'),
                                TextEntry::make('quantity')->label('Product Quantity'),
                                TextEntry::make('price')->label('Product Price')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                                    ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
                            ]), ]),
                    ]),

            ]);
    }
}
