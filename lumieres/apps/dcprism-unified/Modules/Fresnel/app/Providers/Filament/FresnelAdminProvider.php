<?php

namespace Modules\Fresnel\app\Providers\Filament;

use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
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
use Modules\Fresnel\app\Filament\Widgets\DcpStatisticsWidget;
use Modules\Fresnel\app\Filament\Widgets\DcpVersionsOverviewWidget;
use Modules\Fresnel\app\Filament\Widgets\FestivalPerformanceWidget;
use Modules\Fresnel\app\Filament\Widgets\ProcessingActivityWidget;
use Modules\Fresnel\app\Filament\Widgets\StorageUsageWidget;
use Modules\Fresnel\app\Filament\Widgets\TrendsChartWidget;
use Modules\Fresnel\app\Filament\Widgets\UploadTrendsWidget;
use Modules\Fresnel\app\Traits\AppliesGlobalTheme;

class FresnelAdminProvider extends PanelProvider
{
    use AppliesGlobalTheme;

    public function panel(Panel $panel): Panel
    {
        $panel = $this->applyGlobalTheme($panel);

        return $panel
            ->id('fresnel')
            ->path('fresnel/admin')
            ->homeUrl('/fresnel/admin')
            ->default()
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
                // Ressources intégrées dans AdministrationPage (masquées de la navigation)
                \Modules\Fresnel\app\Filament\Resources\Parameters\ParameterResource::class,
                \Modules\Fresnel\app\Filament\Resources\Festivals\FestivalResource::class,
                \Modules\Fresnel\app\Filament\Resources\Users\UserResource::class,
                \Modules\Fresnel\app\Filament\Resources\Langs\LangResource::class,
                \Modules\Fresnel\app\Filament\Resources\Nomenclatures\NomenclatureResource::class,

                // Ressources intégrées dans FilmsPage (masquées de la navigation)
                \Modules\Fresnel\app\Filament\Resources\Movies\MovieResource::class,
                \Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource::class,
                \Modules\Fresnel\app\Filament\Resources\ValidationResults\ValidationResultResource::class,
                \Modules\Fresnel\app\Filament\Resources\MovieMetadata\MovieMetadataResource::class,
                \Modules\Fresnel\app\Filament\Resources\Versions\VersionResource::class,
            ])
            ->discoverPages(in: module_path('Fresnel', 'app/Filament/Pages'), for: 'Modules\\Fresnel\\app\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                \Modules\Fresnel\app\Filament\Pages\AdministrationPage::class,
                \Modules\Fresnel\app\Filament\Pages\FilmsPage::class,
            ])
            ->discoverWidgets(in: module_path('Fresnel', 'app/Filament/Widgets'), for: 'Modules\\Fresnel\\app\\Filament\\Widgets') // RÉACTIVÉ - routes movies disponibles
            ->widgets([
                // Widgets DCP personnalisés organisés par priorité
                DcpVersionsOverviewWidget::class, // Notre nouveau widget Phase 1
                DcpStatisticsWidget::class,
                StorageUsageWidget::class,
                // ProcessingActivityWidget::class, // DÉSACTIVÉ TEMPORAIREMENT
                FestivalPerformanceWidget::class,
                // TrendsChartWidget::class, // DÉSACTIVÉ TEMPORAIREMENT
                UploadTrendsWidget::class,
                \Modules\Fresnel\app\Filament\Widgets\StatsOverview::class,
                \Modules\Fresnel\app\Filament\Widgets\LatestMovies::class, // RÉACTIVÉ - routes movies disponibles

                // Widgets par défaut
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // AuthenticateSession::class, // VIRE
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class, // Nécessaire pour Filament
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\FilamentAuthenticate::class,
                'panel.access:panel.admin',
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->databaseNotifications(); // Active le système de notifications natif
    }
}
