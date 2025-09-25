<?php

namespace Modules\Fresnel\app\Filament\Cinema\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\User;
use UnitEnum;

class ScreenSettingsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationLabel = 'Gestion Salles';

    protected static ?string $title = 'Configuration des Salles';

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected string $view = 'filament.pages.cinema.screen-settings';

    public string $activeTab = 'screens';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('cinema');
    }

    public function mount(): void
    {
        // Initialisation des salles du cinéma
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'screenCategories' => $this->getScreenCategories(),
            'totalScreens' => $this->getTotalScreens(),
            'activeTab' => $this->activeTab,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes selon l'onglet
            ])
            ->filters([
                // À implémenter : filtres par type, statut, capacité
            ])
            ->actions([
                // À implémenter : actions config, test, maintenance
            ]);
    }

    protected function getTableQuery()
    {
        return match ($this->activeTab) {
            'screens' => $this->getScreensQuery(),
            'projectors' => $this->getProjectorsQuery(),
            'audio' => $this->getAudioQuery(),
            'maintenance' => $this->getMaintenanceQuery(),
            default => $this->getScreensQuery(),
        };
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    private function getScreenCategories(): array
    {
        return [
            'screens' => 'Salles de projection',
            'projectors' => 'Projecteurs numériques',
            'audio' => 'Systèmes audio',
            'maintenance' => 'Planning maintenance',
        ];
    }

    private function getTotalScreens(): int
    {
        // À implémenter : nombre total de salles
        return 0;
    }

    private function getScreensQuery()
    {
        // À implémenter : liste des salles du cinéma
        return User::query()->whereRaw('1 = 0');
    }

    private function getProjectorsQuery()
    {
        // À implémenter : liste des projecteurs
        return User::query()->whereRaw('1 = 0');
    }

    private function getAudioQuery()
    {
        // À implémenter : systèmes audio
        return User::query()->whereRaw('1 = 0');
    }

    private function getMaintenanceQuery()
    {
        // À implémenter : planning de maintenance
        return User::query()->whereRaw('1 = 0');
    }
}
