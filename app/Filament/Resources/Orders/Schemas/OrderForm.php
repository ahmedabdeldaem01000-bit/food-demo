<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(null),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('usd'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('paypal_order_id')
                    ->default(null),
                TextInput::make('stripe_session_id')
                    ->default(null),
            ]);
    }
}
