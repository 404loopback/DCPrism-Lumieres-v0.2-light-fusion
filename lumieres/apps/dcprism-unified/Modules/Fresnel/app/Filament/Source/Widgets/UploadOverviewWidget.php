<?php

namespace Modules\Fresnel\app\Filament\Source\Widgets;

use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Filament\Shared\Widgets\BaseFestivalAwareWidget;

/**
 * Widget des statistiques d'upload pour les Sources
 * Affiche les stats des uploads DCP filtrées par festival sélectionné
 */
class UploadOverviewWidget extends BaseFestivalAwareWidget
{
    protected static ?int $sort = 2;
    protected string $noFestivalMessage = 'Sélectionnez un festival pour voir vos uploads';
    
    protected function getFestivalSpecificStats(): array
    {
        // Filtrer les DCPs par festival via les movies
        $baseQuery = $this->scopeByFestival(Dcp::query());
        
        $todayUploads = (clone $baseQuery)->whereDate('created_at', today())->count();
        $totalUploads = (clone $baseQuery)->count();
        $pendingProcessing = (clone $baseQuery)->whereIn('status', ['uploaded', 'processing'])->count();
        $successfulUploads = (clone $baseQuery)->whereIn('status', ['validated', 'ready'])->count();
        
        return [
            $this->createFestivalStat(
                "Uploads Aujourd'hui", 
                $todayUploads,
                'Fichiers envoyés',
                'heroicon-m-arrow-up-tray',
                'primary'
            ),
                
            $this->createFestivalStat(
                'Total Uploads', 
                $totalUploads,
                'Tous les DCP',
                'heroicon-m-document-arrow-up',
                'info'
            ),
                
            $this->createStat(
                'En Traitement', 
                $pendingProcessing,
                'Files en cours',
                'heroicon-m-arrows-pointing-in',
                'warning'
            ),
                
            $this->createStat(
                'Uploads Réussis', 
                $successfulUploads,
                'Prêts pour utilisation',
                'heroicon-m-check-badge',
                'success'
            ),
        ];
    }
}
