<?php

namespace Modules\Fresnel\app\Providers\Filament;

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
use Modules\Fresnel\app\Filament\Tech\Widgets\TechnicalValidationWidget;

class TechPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tech')
            ->path('fresnel/tech')
            ->authGuard('web')
            ->brandName('DCPrism Tech')
            ->brandLogo(asset('images/logo-dcprism.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Orange,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: module_path('Fresnel', 'app/Filament/Tech/Resources'), for: 'Modules\\Fresnel\\app\\Filament\\Tech\\Resources')
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Tech/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Tech\\Pages')
            // Découverte des pages partagées
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Pages')
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Shared/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Shared\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: module_path('Fresnel', 'app/Filament/Tech/Widgets'), for: 'Modules\\Fresnel\\app\\Filament\\Tech\\Widgets')
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
                // DisableBladeIconComponents::class, // RETIRÉ POUR PERMETTRE L'AFFICHAGE DES ICÔNES
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'panel.access:panel.tech',
            ])
            ->databaseNotifications(); // Active le système de notifications natif
    }
}
