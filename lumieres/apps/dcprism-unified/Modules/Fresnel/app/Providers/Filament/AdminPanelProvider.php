<?php

namespace Modules\Fresnel\app\Providers\Filament;

use Modules\Fresnel\app\Traits\AppliesGlobalTheme;
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
use Modules\Fresnel\app\Filament\Widgets\DcpStatisticsWidget;
use Modules\Fresnel\app\Filament\Widgets\ProcessingActivityWidget;
use Modules\Fresnel\app\Filament\Widgets\StorageUsageWidget;
use Modules\Fresnel\app\Filament\Widgets\FestivalPerformanceWidget;
use Modules\Fresnel\app\Filament\Widgets\TrendsChartWidget;
use Modules\Fresnel\app\Filament\Widgets\UploadTrendsWidget;
use Modules\Fresnel\app\Filament\Widgets\DcpVersionsOverviewWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;
use Modules\Fresnel\app\Http\Middleware\FilamentRoleRedirect;

class AdminPanelProvider extends PanelProvider
{
    use AppliesGlobalTheme;
    
    // Temporairement désactivé pour debug
    // public function canAccess(): bool
    // {
    //     return Auth::check() && Auth::user()->role === 'admin';
    // }
    public function panel(Panel $panel): Panel
    {
        $panel = $this->applyGlobalTheme($panel);
        
        return $panel
            ->default()
            ->id('fresnel')
            ->path('fresnel')
            ->login(\Modules\Fresnel\app\Filament\Pages\Auth\Login::class)
            ->authGuard('web')
            ->brandName('DCPrism - Connexion')
            ->brandLogo(asset('images/logo-dcprism.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
            ])
            ->resources([
                \Modules\Fresnel\app\Filament\Resources\Parameters\ParameterResource::class,
                \Modules\Fresnel\app\Filament\Resources\Movies\MovieResource::class,
                \Modules\Fresnel\app\Filament\Resources\Festivals\FestivalResource::class,
                \Modules\Fresnel\app\Filament\Resources\Users\UserResource::class,
                \Modules\Fresnel\app\Filament\Resources\Langs\LangResource::class,
                \Modules\Fresnel\app\Filament\Resources\Nomenclatures\NomenclatureResource::class,
                \Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource::class,
                \Modules\Fresnel\app\Filament\Resources\Versions\VersionResource::class,
                \Modules\Fresnel\app\Filament\Resources\ValidationResults\ValidationResultResource::class,
                \Modules\Fresnel\app\Filament\Resources\MovieMetadata\MovieMetadataResource::class,
            ])
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            // ->discoverWidgets(in: module_path('Fresnel', 'app/Filament/Widgets'), for: 'Modules\\Fresnel\\app\\Filament\\Widgets')
            ->widgets([
                // Widgets DCP personnalisés organisés par priorité
                DcpVersionsOverviewWidget::class, // Notre nouveau widget Phase 1
                DcpStatisticsWidget::class,
                StorageUsageWidget::class,
                ProcessingActivityWidget::class,
                FestivalPerformanceWidget::class,
                TrendsChartWidget::class,
                UploadTrendsWidget::class,
                
                // Widgets par défaut
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
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // FilamentRoleRedirect::class, // Temporairement désactivé pour debug
            ])
            ->databaseNotifications(); // Active le système de notifications natif
    }
}
