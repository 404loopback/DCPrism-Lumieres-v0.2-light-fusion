<?php

namespace Modules\Fresnel\app\Filament\Manager\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class FestivalSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationLabel = 'Paramètres Festival';

    protected static ?string $title = 'Configuration Festival';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration Festival';

    protected string $view = 'filament.pages.manager.festival-settings';

    public string $activeSection = 'general';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('manager');
    }

    public function mount(): void
    {
        // Initialisation des paramètres du festival sélectionné
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'selectedFestival' => $this->getSelectedFestival(),
            'festivalSettings' => $this->getFestivalSettings(),
            'activeSection' => $this->activeSection,
        ];
    }

    public function changeSection(string $section): void
    {
        $this->activeSection = $section;
    }

    private function getSelectedFestival()
    {
        $festivalId = session('selected_festival_id');
        if (!$festivalId) {
            return null;
        }

        return auth()->user()?->festivals()->find($festivalId);
    }

    private function getFestivalSettings(): array
    {
        // À implémenter : récupérer les paramètres configurables
        return [
            'general' => [], // Nom, description, dates
            'submissions' => [], // Deadline soumissions, règles
            'technical' => [], // Formats acceptés, qualité
            'notifications' => [], // Emails, alertes
        ];
    }
}
