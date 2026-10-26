<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentCustomers extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminDashboard') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['admin', 'driver', 'employee']))
                    ->withCount('order')
                    ->latest('created_at')
            )
            ->heading('Latest customers')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('order_count')->label('Orders')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10]);
    }
}
