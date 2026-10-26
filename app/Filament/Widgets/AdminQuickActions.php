<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminQuickActions extends Widget
{
    protected static ?int $sort = 7;

    protected string $view = 'filament.widgets.admin-quick-actions';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminDashboard') ?? false;
    }
}
