<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\VersionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Filament\Manager\Resources\VersionResource;

class ListVersions extends ListRecords
{
    protected static string $resource = VersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Les managers ne créent généralement pas de versions directement
            // Les versions sont créées lors de la création de films
            Actions\Action::make('info')
                ->label('Information')
                ->icon('heroicon-o-information-circle')
                ->color('info')
                ->modalHeading('À propos des versions')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->modalContent(fn () => view('fresnel::filament.modals.versions-info')),
        ];
    }

    public function getTitle(): string
    {
        $festivalId = Session::get('selected_festival_id');

        if ($festivalId) {
            $festival = \Modules\Fresnel\app\Models\Festival::find($festivalId);

            return $festival ? "Versions - {$festival->name}" : 'Versions';
        }

        return 'Versions';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Widgets statistiques pour les versions (à implémenter selon les besoins)
            // Exemples possibles :
            // - VersionStatsWidget::class (total versions, répartition par type VO/VF/VOST)
            // - VersionProgressWidget::class (statut validation, upload, etc.)
            // - VersionChartWidget::class (graphiques évolution temporelle)
        ];
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function getSubheading(): ?string
    {
        $festivalId = Session::get('selected_festival_id');

        if (! $festivalId) {
            return 'Aucun festival sélectionné. Veuillez retourner au dashboard et choisir un festival à administrer.';
        }

        $count = $this->getTableQuery()->count();

        return "Gestion des versions linguistiques ({$count} version".($count > 1 ? 's' : '').')';
    }

    /**
     * Override pour s'assurer que le festival est sélectionné
     */
    public function mount(): void
    {
        parent::mount();

        // Pas de redirection forcée, on laisse getEloquentQuery() gérer le cas où il n'y a pas de festival
    }
}
