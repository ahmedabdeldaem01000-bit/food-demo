<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment record')
                ->schema([
                    TextEntry::make('id')->label('Payment ID'),
                    TextEntry::make('order_id')->label('Order ID'),
                    TextEntry::make('method')->badge(),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('amount')
                        ->formatStateUsing(fn ($state, $record): string => number_format((float) $state, 2).' '.strtoupper($record->currency)),
                    TextEntry::make('paypal_capture_id')->placeholder('Not provided'),
                    TextEntry::make('created_at')->dateTime(),
                ])
                ->columns(2),
        ]);
    }
}
