<?php

namespace Modules\Fresnel\app\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Modules\Fresnel\app\Filament\Source\Pages\SourceDashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SourcePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('source')
            ->path('fresnel/source')
            ->authGuard('web')
            ->brandName('DCPrism Source')
            ->brandLogo(asset('images/logo-dcprism.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Cyan,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: module_path('Fresnel', 'app/Filament/Source/Resources'), for: 'Modules\\Fresnel\\app\\Filament\\Source\\Resources')
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Source/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Source\\Pages')
            ->pages([
                SourceDashboard::class,
            ])
            ->discoverWidgets(in: module_path('Fresnel', 'app/Filament/Source/Widgets'), for: 'Modules\\Fresnel\\app\\Filament\\Source\\Widgets')
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
                'panel.access:panel.source',
            ])
            ->databaseNotifications(); // Active le système de notifications natif
    }
}
