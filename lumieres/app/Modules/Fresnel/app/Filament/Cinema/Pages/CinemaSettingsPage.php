<?php

namespace Modules\Fresnel\app\Filament\Cinema\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class CinemaSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Paramètres Cinéma';

    protected static ?string $title = 'Configuration Cinéma';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected string $view = 'filament.pages.cinema.cinema-settings';

    public string $activeSection = 'general';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('cinema');
    }

    public function mount(): void
    {
        // Initialisation des paramètres du cinéma
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'cinemaInfo' => $this->getCinemaInfo(),
            'settingsSections' => $this->getSettingsSections(),
            'activeSection' => $this->activeSection,
        ];
    }

    public function changeSection(string $section): void
    {
        $this->activeSection = $section;
    }

    private function getCinemaInfo(): array
    {
        // À implémenter : informations du cinéma de l'utilisateur
        return [
            'name' => 'Nom du cinéma',
            'address' => 'Adresse',
            'contact' => 'Informations de contact',
            'technical_specs' => 'Spécifications techniques',
        ];
    }

    private function getSettingsSections(): array
    {
        return [
            'general' => 'Informations générales',
            'technical' => 'Configuration technique',
            'projection' => 'Paramètres projection',
            'notifications' => 'Alertes et notifications',
        ];
    }
}
