<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
TextColumn::make('name')
    ->searchable()
    ->sortable(),

ImageColumn::make('image')
    ->square()
         ->disk('public')
    ->url(fn ($record) => asset('storage/' . $record->image)),

TextColumn::make('kcal')
    ->label('Calories')
    ->suffix(' kcal')
    ->sortable(),

TextColumn::make('price')
    ->money('USD')
    ->sortable(),

TextColumn::make('review')
    ->label('Rating')
    ->formatStateUsing(fn ($state) => $state . ' ⭐')
    ->sortable(),

TextColumn::make('category.name') // 👈 بدل category_id
    ->label('Category')
    ->sortable()
    ->searchable(),

TextColumn::make('created_at')
    ->dateTime('Y-m-d H:i')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),

TextColumn::make('updated_at')
    ->dateTime('Y-m-d H:i')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
                ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
