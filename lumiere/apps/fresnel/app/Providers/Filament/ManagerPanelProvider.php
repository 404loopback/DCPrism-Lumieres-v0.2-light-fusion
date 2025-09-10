<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Manager\Pages\ManagerDashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Manager\Widgets\FestivalOverviewWidget;
use App\Filament\Manager\Widgets\FestivalSelectorWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ManagerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->viteTheme('resources/css/filament/theme.css')
            ->id('manager')
            ->path('panel/manager')
            ->authGuard('web')
            ->brandName('DCPrism Manager')
            ->brandLogo(asset('images/logo-dcprism.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Green,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Manager/Resources'), for: 'App\Filament\Manager\Resources')
            ->discoverPages(in: app_path('Filament/Manager/Pages'), for: 'App\Filament\Manager\Pages')
            ->pages([
                ManagerDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Manager/Widgets'), for: 'App\Filament\Manager\Widgets')
            ->widgets([
                // Widgets spécialisés pour la gestion festival
                FestivalSelectorWidget::class,
                FestivalOverviewWidget::class,
                AccountWidget::class,
            ])
            ->databaseNotifications() // Active le système de notifications natif
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
