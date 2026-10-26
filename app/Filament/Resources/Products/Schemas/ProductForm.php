<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu item details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->columns(),
                    TextInput::make('slug')
                        ->disabled()
                        ->dehydrated(false),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Textarea::make('description')
                        ->default(null)
                        ->columnSpanFull(),

                    KeyValue::make('ingredients')
                        ->label('Ingredients')
                        ->keyLabel('name')
                        ->valueLabel('property')
                        ->addActionLabel('Add property')
                        ->required()->columnSpanFull(),
                ])->columns(2),

                Section::make('Image and pricing')->schema([
                    FileUpload::make('image')
                        ->image()
                        ->maxParallelUploads(1)
                        ->imageEditor()
                        ->directory('images/products')
                        ->disk('public')
                        ->visibility('public')
                        ->imageEditorAspectRatioOptions([
                            null,
                            '16:9',
                            '4:3',
                            '1:1',
                        ]),

                    TextInput::make('kcal')
                        ->label('Calories (Kcal)')
                        ->numeric()
                        ->minValue(0)
                        ->nullable()
                        ->suffix('kcal'),

                    TextInput::make('price')
                        ->label('Price')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->suffix('EGP')
                        ->rule('decimal:0,2'),

                    TextInput::make('review')
                        ->label('Rating')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(5)
                        ->default(0)
                        ->step(0.1),

                ]),

            ]);
    }
}
