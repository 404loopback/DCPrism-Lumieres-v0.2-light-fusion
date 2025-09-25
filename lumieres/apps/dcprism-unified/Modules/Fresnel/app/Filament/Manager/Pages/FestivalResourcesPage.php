<?php

namespace Modules\Fresnel\app\Filament\Manager\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\User;
use UnitEnum;

class FestivalResourcesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Ressources Festival';

    protected static ?string $title = 'Ressources du Festival';

    protected static ?int $navigationSort = 6;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion Festival';

    protected string $view = 'filament.pages.manager.festival-resources';

    public string $activeTab = 'documents';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('manager');
    }

    public function mount(): void
    {
        // Initialisation des ressources du festival
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'selectedFestival' => $this->getSelectedFestival(),
            'resourceCategories' => $this->getResourceCategories(),
            'activeTab' => $this->activeTab,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes pour les ressources
            ])
            ->filters([
                // À implémenter : filtres par type, statut
            ])
            ->actions([
                // À implémenter : actions upload, download, partage
            ]);
    }

    protected function getTableQuery()
    {
        // À implémenter : requête selon le type de ressource
        return match ($this->activeTab) {
            'documents' => $this->getDocumentsQuery(),
            'templates' => $this->getTemplatesQuery(),
            'media' => $this->getMediaQuery(),
            'reports' => $this->getReportsQuery(),
            default => $this->getDocumentsQuery(),
        };
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    private function getSelectedFestival()
    {
        $festivalId = session('selected_festival_id');
        if (!$festivalId) {
            return null;
        }

        return auth()->user()?->festivals()->find($festivalId);
    }

    private function getResourceCategories(): array
    {
        return [
            'documents' => 'Documents administratifs',
            'templates' => 'Modèles et formulaires', 
            'media' => 'Assets visuels',
            'reports' => 'Rapports et statistiques',
        ];
    }

    private function getDocumentsQuery()
    {
        // À implémenter : documents du festival
        // Retourner un Builder vide basé sur User pour l'instant
        return User::query()->whereRaw('1 = 0');
    }

    private function getTemplatesQuery()
    {
        // À implémenter : templates réutilisables
        return User::query()->whereRaw('1 = 0');
    }

    private function getMediaQuery()
    {
        // À implémenter : assets média
        return User::query()->whereRaw('1 = 0');
    }

    private function getReportsQuery()
    {
        // À implémenter : rapports générés
        return User::query()->whereRaw('1 = 0');
    }
}
