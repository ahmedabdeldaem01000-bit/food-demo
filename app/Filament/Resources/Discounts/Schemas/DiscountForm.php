<?php

namespace App\Filament\Resources\Discounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Offer details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (?string $state): string => Str::upper(trim($state ?? ''))),
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                    Select::make('type')
                        ->options([
                            'percentage' => 'Percentage',
                            'fixed' => 'Fixed amount',
                        ])
                        ->required()
                        ->live(),
                    TextInput::make('value')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get): ?int => $get('type') === 'percentage' ? 100 : null)
                        ->label(fn (Get $get): string => $get('type') === 'percentage' ? 'Discount percentage' : 'Discount amount')
                        ->suffix(fn (Get $get): string => $get('type') === 'percentage' ? '%' : 'EGP')
                        ->rule('decimal:0,2'),
                    TextInput::make('minimum_order_amount')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->suffix('EGP')
                        ->required(),
                    TextInput::make('usage_limit')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->nullable()
                        ->helperText('Leave empty for unlimited uses.'),
                ])
                ->columns(2),
            Section::make('Availability')
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->seconds(false)
                        ->nullable(),
                    DateTimePicker::make('ends_at')
                        ->seconds(false)
                        ->after('starts_at')
                        ->nullable(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(false)
                        ->helperText('Activating this discount sends an offer notification to regular users.')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
