<?php

namespace App\Filament\Shared\Widgets;

use App\Filament\Shared\Concerns\HasFestivalContext;
use App\Models\Festival;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Classe de base pour les widgets qui dépendent du festival sélectionné
 * Combine BaseStatsWidget avec la logique de contexte festival
 */
abstract class BaseFestivalAwareWidget extends BaseStatsWidget
{
    use HasFestivalContext;
    
    /**
     * Message à afficher quand aucun festival n'est sélectionné
     */
    protected string $noFestivalMessage = 'Veuillez sélectionner un festival';
    
    /**
     * Implémentation finale qui gère le contexte festival
     */
    final protected function getStatsData(): array
    {
        // Si aucun festival sélectionné, afficher un message d'info
        if (!$this->hasFestivalSelected()) {
            return [
                $this->createStat(
                    'Aucun festival sélectionné', 
                    0,
                    $this->noFestivalMessage,
                    'heroicon-m-exclamation-triangle',
                    'warning'
                )
            ];
        }
        
        // Déléguer aux widgets spécialisés
        return $this->getFestivalSpecificStats();
    }
    
    /**
     * Méthode abstraite que chaque widget spécialisé doit implémenter
     */
    abstract protected function getFestivalSpecificStats(): array;
    
    /**
     * Utilitaire : Créer une stat avec le nom du festival dans la description
     */
    protected function createFestivalStat(
        string $label, 
        int|string $value, 
        string $description = null, 
        string $icon = null, 
        string $color = 'primary',
        array $chart = null
    ): Stat {
        $festival = $this->getSelectedFestival();
        $festivalName = $festival ? $festival->name : 'Festival sélectionné';
        
        $fullDescription = $description ? "{$description} pour {$festivalName}" : "pour {$festivalName}";
        
        return $this->createStat($label, $value, $fullDescription, $icon, $color, $chart);
    }
    
    /**
     * Utilitaire : Appliquer le scope festival à une requête
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeByFestival($query)
    {
        return $this->scopeBySelectedFestival($query);
    }
    
    /**
     * Utilitaire : Obtenir les statistiques d'aujourd'hui pour le festival
     */
    protected function getTodayStatsForFestival($model, string $relation = 'festivals'): int
    {
        return $model::whereHas($relation, function ($query) {
                $query->where('festivals.id', $this->getSelectedFestival()?->id);
            })
            ->whereDate('created_at', today())
            ->count();
    }
    
    /**
     * Utilitaire : Obtenir les statistiques totales pour le festival
     */
    protected function getTotalStatsForFestival($model, string $relation = 'festivals'): int
    {
        return $model::whereHas($relation, function ($query) {
                $query->where('festivals.id', $this->getSelectedFestival()?->id);
            })
            ->count();
    }
    
    /**
     * Utilitaire : Obtenir les statistiques avec statut pour le festival
     */
    protected function getStatusStatsForFestival($model, string $status, string $relation = 'festivals'): int
    {
        return $model::where('status', $status)
            ->whereHas($relation, function ($query) {
                $query->where('festivals.id', $this->getSelectedFestival()?->id);
            })
            ->count();
    }
}
