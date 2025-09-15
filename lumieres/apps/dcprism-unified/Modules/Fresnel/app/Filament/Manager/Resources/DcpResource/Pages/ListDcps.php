<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\DcpResource\Pages;

use Modules\Fresnel\app\Filament\Manager\Resources\DcpResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Models\Dcp;

class ListDcps extends ListRecords
{
    protected static string $resource = DcpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('stats')
                ->label('Statistiques')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Statistiques des DCPs')
->modalContent(view('fresnel::filament.modals.dcp-stats', $this->getDcpStatsData()))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
            Actions\Action::make('bulk_validate')
                ->label('Validation par lots')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Validation par lots')
                ->modalDescription('Valider tous les DCPs en attente de validation pour ce festival ?')
                ->action(function () {
                    $this->bulkValidateDcps();
                })
                ->visible(function () {
                    // Visible seulement s'il y a des DCPs en attente
                    return $this->getTableQuery()
                        ->where('status', Dcp::STATUS_UPLOADED)
                        ->where('is_valid', false)
                        ->exists();
                }),
        ];
    }

    public function getTitle(): string
    {
        $festivalId = Session::get('selected_festival_id');
        
        if ($festivalId) {
            $festival = \Modules\Fresnel\app\Models\Festival::find($festivalId);
            return $festival ? "DCPs - {$festival->name}" : 'DCPs';
        }
        
        return 'DCPs';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // TODO: Ajouter des widgets statistiques pour les DCPs
            // DcpResource\Widgets\DcpStatsWidget::class,
        ];
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function getSubheading(): ?string
    {
        $festivalId = Session::get('selected_festival_id');
        
        if (!$festivalId) {
            return 'Aucun festival sélectionné. Veuillez retourner au dashboard et choisir un festival à administrer.';
        }
        
        $query = $this->getTableQuery();
        $total = $query->count();
        $validated = $query->where('is_valid', true)->count();
        $pending = $query->where('status', Dcp::STATUS_UPLOADED)->where('is_valid', false)->count();
        
        return "Gestion des DCPs - Total: {$total}, Validés: {$validated}, En attente: {$pending}";
    }

    /**
     * Generate DCP statistics modal content
     */
    protected function getDcpStats()
    {
        $data = $this->getDcpStatsData();
        return view('fresnel::filament.modals.dcp-stats', $data);
    }
    
    /**
     * Get DCP statistics data
     */
    protected function getDcpStatsData(): array
    {
        $query = $this->getTableQuery();
        
        $stats = [
            'total' => $query->count(),
            'valid' => $query->where('is_valid', true)->count(),
            'pending' => $query->where('status', Dcp::STATUS_UPLOADED)->where('is_valid', false)->count(),
            'invalid' => $query->where('status', Dcp::STATUS_INVALID)->count(),
            'processing' => $query->where('status', Dcp::STATUS_PROCESSING)->count(),
            'error' => $query->where('status', Dcp::STATUS_ERROR)->count(),
        ];

        // Statistiques par version
        $versionStats = $query
            ->join('versions', 'dcps.version_id', '=', 'versions.id')
            ->groupBy('versions.type')
            ->selectRaw('versions.type, count(*) as count')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'stats' => $stats,
            'versionStats' => $versionStats
        ];
    }

    /**
     * Bulk validate DCPs
     */
    protected function bulkValidateDcps(): void
    {
        try {
            $dcps = $this->getTableQuery()
                ->where('status', Dcp::STATUS_UPLOADED)
                ->where('is_valid', false)
                ->get();

            $validated = 0;
            foreach ($dcps as $dcp) {
                $dcp->markAsValid('Validation par lots - Manager');
                $validated++;
            }

            \Filament\Notifications\Notification::make()
                ->title('Validation par lots terminée')
                ->body("{$validated} DCP(s) ont été validés avec succès")
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Erreur de validation par lots')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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
