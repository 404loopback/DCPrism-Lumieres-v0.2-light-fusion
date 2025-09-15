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

class CinemaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cinema')
            ->path('fresnel/cinema')
            ->authGuard('web')
            ->brandName('DCPrism Cinema')
            ->brandLogo(asset('images/logo-dcprism.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Red,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: module_path('Fresnel', 'app/Filament/Cinema/Resources'), for: 'Modules\\Fresnel\\app\\Filament\\Cinema\\Resources')
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Cinema/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Cinema\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: module_path('Fresnel', 'app/Filament/Cinema/Widgets'), for: 'Modules\\Fresnel\\app\\Filament\\Cinema\\Widgets')
            ->widgets([
                // Widgets spécialisés pour les cinémas partenaires
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
            ]);
    }
}
