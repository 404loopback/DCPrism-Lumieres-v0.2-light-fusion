<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Upload;
use Modules\Fresnel\app\Filament\Shared\Widgets\BaseStatsWidget;

/**
 * Widget des statistiques DCP globales
 * Affiche les métriques principales du système DCP
 */
class DcpStatisticsWidget extends BaseStatsWidget
{
    protected static ?int $sort = 1;
    
    protected function getStatsData(): array
    {
        $totalMovies = Movie::count();
        $validationRate = $this->getValidationRate();
        
        return [
            $this->createStat(
                'Films totaux', 
                $totalMovies,
                'Total des films dans le système',
                'heroicon-o-film',
                'primary',
                $this->generateSampleChart(12)
            ),
                
            $this->createStat(
                'En traitement', 
                Movie::where('status', 'processing')->count(),
                'DCP en cours de traitement',
                'heroicon-o-cog-6-tooth',
                'warning',
                $this->generateSampleChart(12)
            ),
                
            $this->createStat(
                'Validés', 
                Movie::where('status', 'validated')->count(),
                'DCP validés et prêts',
                'heroicon-o-check-circle',
                'success',
                $this->generateSampleChart(12)
            ),
                
            $this->createStat(
                'Échecs', 
                Movie::where('status', 'failed')->count(),
                'DCP avec erreurs',
                'heroicon-o-x-circle',
                'danger',
                $this->generateSampleChart(12)
            ),
                
            $this->createStat(
                'Uploads aujourd\'hui', 
                Upload::whereDate('created_at', today())->count(),
                'Fichiers uploadés aujourd\'hui',
                'heroicon-o-arrow-up-tray',
                'info',
                $this->generateSampleChart(12)
            ),
                
            $this->createStat(
                'Taux de validation', 
                $validationRate . '%',
                '% des DCP validés avec succès',
                'heroicon-o-chart-bar',
                $this->getPercentageColor($validationRate),
                $this->generateSampleChart(12)
            ),
        ];
    }
    
    private function getValidationRate(): int
    {
        $totalMovies = Movie::count();
        $validatedMovies = Movie::where('status', 'validated')->count();
        return $this->calculatePercentage($validatedMovies, $totalMovies);
    }
}
