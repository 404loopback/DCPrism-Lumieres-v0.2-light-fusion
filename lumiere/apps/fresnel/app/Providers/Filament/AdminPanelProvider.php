<?php

namespace App\Providers\Filament;

use App\Traits\AppliesGlobalTheme;
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
use App\Filament\Widgets\DcpStatisticsWidget;
use App\Filament\Widgets\ProcessingActivityWidget;
use App\Filament\Widgets\StorageUsageWidget;
use App\Filament\Widgets\FestivalPerformanceWidget;
use App\Filament\Widgets\TrendsChartWidget;
use App\Filament\Widgets\UploadTrendsWidget;
use App\Filament\Widgets\DcpVersionsOverviewWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\FilamentRoleRedirect;

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
            ->id('admin')
            ->path('panel/admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
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
                \App\Filament\Resources\Parameters\ParameterResource::class,
                \App\Filament\Resources\Movies\MovieResource::class,
                \App\Filament\Resources\Festivals\FestivalResource::class,
                \App\Filament\Resources\Users\UserResource::class,
                \App\Filament\Resources\Langs\LangResource::class,
                \App\Filament\Resources\Nomenclatures\NomenclatureResource::class,
                \App\Filament\Resources\Dcps\DcpResource::class,
                \App\Filament\Resources\Versions\VersionResource::class,
                \App\Filament\Resources\ValidationResults\ValidationResultResource::class,
                \App\Filament\Resources\MovieMetadata\MovieMetadataResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Widgets DCP personnalisés organisés par priorité
                DcpVersionsOverviewWidget::class, // Notre nouveau widget Phase 1
                DcpStatisticsWidget::class,
                StorageUsageWidget::class,
                ProcessingActivityWidget::class,
                FestivalPerformanceWidget::class,
                TrendsChartWidget::class,
                UploadTrendsWidget::class,
                
                
                // Widgets par défaut (cachés ou en bas)
                AccountWidget::class,
                // FilamentInfoWidget::class, // Masqué pour un dashboard plus propre
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
