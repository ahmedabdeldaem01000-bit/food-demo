<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AdminDashboardAlerts;
use App\Filament\Widgets\AdminOrderStatusChart;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\AdminQuickActions;
use App\Filament\Widgets\AdminSalesTrendChart;
use App\Filament\Widgets\AdminTopProductsChart;
use App\Filament\Widgets\EmployeeOverview;
use App\Filament\Widgets\OrdersTrendChart;
use App\Filament\Widgets\RecentOrders;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('Foodify')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminOverview::class,
                AdminDashboardAlerts::class,
                AdminOrderStatusChart::class,
                AdminTopProductsChart::class,
                EmployeeOverview::class,
                OrdersTrendChart::class,
                AdminSalesTrendChart::class,
                RecentOrders::class,
                AdminQuickActions::class,
                AccountWidget::class,

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,

            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

    }
}
