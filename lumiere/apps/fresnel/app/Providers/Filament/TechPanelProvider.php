<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Tech\Widgets\TechnicalValidationWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TechPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tech')
            ->path('panel/tech')
            ->authGuard('web')
            ->brandName('DCPrism Tech')
            ->brandLogo(asset('images/logo-dcprism.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Orange,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Tech/Resources'), for: 'App\Filament\Tech\Resources')
            ->discoverPages(in: app_path('Filament/Tech/Pages'), for: 'App\Filament\Tech\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tech/Widgets'), for: 'App\Filament\Tech\Widgets')
            ->widgets([
                // Widgets spécialisés pour la validation technique
                TechnicalValidationWidget::class,
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
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\FilamentRoleRedirect::class,
            ])
            ->databaseNotifications(); // Active le système de notifications natif
    }
}
