<?php

namespace Modules\Meniscus\app\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MeniscusPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('meniscus')
            ->path('meniscus')
            ->login(\Modules\Meniscus\app\Filament\Pages\Auth\Login::class)
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: module_path('Meniscus', 'app/Filament/Resources'), for: 'Modules\\Meniscus\\app\\Filament\\Resources')
            ->discoverPages(in: module_path('Meniscus', 'app/Filament/Pages'), for: 'Modules\\Meniscus\\app\\Filament\\Pages')
            // ->discoverWidgets() supprimé pour éviter les doublons
            ->pages([
                \Modules\Meniscus\app\Filament\Pages\Dashboard::class,
            ])
            ->widgets([
                // Seulement les widgets Filament par défaut
                // Les widgets personnalisés sont gérés dans Dashboard::getHeaderWidgets()
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ]);
    }
}
