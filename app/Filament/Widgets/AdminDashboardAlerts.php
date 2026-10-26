<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminReportService;
use Filament\Widgets\Widget;

class AdminDashboardAlerts extends Widget
{
    protected static ?int $sort = 6;

    protected string $view = 'filament.widgets.admin-dashboard-alerts';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:AdminDashboard') ?? false;
    }

    protected function getViewData(): array
    {
        return ['alerts' => app(AdminReportService::class)->dashboardAlerts()];
    }
}
