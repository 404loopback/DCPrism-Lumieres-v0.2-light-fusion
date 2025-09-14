<?php

namespace Modules\Fresnel\app\Filament\Shared\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Classe de base pour tous les widgets de statistiques
 * Fournit des méthodes utilitaires communes et standardise l'apparence
 */
abstract class BaseStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    /**
     * Configuration par défaut pour tous les widgets de stats
     */
    protected int | string | array $columnSpan = 'full';
    
    /**
     * Polling par défaut (peut être surchargé)
     * DÉSACTIVÉ TEMPORAIREMENT POUR DEBUG
     */
    protected ?string $pollingInterval = null;
    
    /**
     * Méthode abstraite que chaque widget doit implémenter
     */
    abstract protected function getStatsData(): array;
    
    /**
     * Implémentation finale qui standardise l'affichage
     */
    final protected function getStats(): array
    {
        $stats = $this->getStatsData();
        
        // Appliquer le styling standardisé
        return array_map(function($stat) {
            return $this->applyStandardStyling($stat);
        }, $stats);
    }
    
    /**
     * Applique un styling standardisé à toutes les stats
     */
    protected function applyStandardStyling(Stat $stat): Stat
    {
        // Styles communs pour tous les widgets
        return $stat;
    }
    
    /**
     * Utilitaire : Créer une stat avec format standard
     */
    protected function createStat(
        string $label, 
        int|string $value, 
        string $description = null, 
        string $icon = null, 
        string $color = 'primary',
        array $chart = null
    ): Stat {
        $stat = Stat::make($label, $value);
        
        if ($description) {
            $stat->description($description);
        }
        
        if ($icon) {
            $stat->descriptionIcon($icon);
        }
        
        $stat->color($color);
        
        if ($chart) {
            $stat->chart($chart);
        }
        
        return $stat;
    }
    
    /**
     * Utilitaire : Calculer un pourcentage
     */
    protected function calculatePercentage(int $numerator, int $denominator): int
    {
        if ($denominator === 0) return 0;
        return (int) round(($numerator / $denominator) * 100);
    }
    
    /**
     * Utilitaire : Formatter une taille en bytes
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Utilitaire : Formatter une durée en secondes
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . 'min';
        } else {
            return round($seconds / 3600, 1) . 'h';
        }
    }
    
    /**
     * Utilitaire : Générer un graphique de démonstration
     */
    protected function generateSampleChart(int $length = 12): array
    {
        return array_map(fn() => rand(1, 30), range(1, $length));
    }
    
    /**
     * Utilitaire : Couleur basée sur un pourcentage
     */
    protected function getPercentageColor(int $percentage): string
    {
        if ($percentage >= 90) return 'success';
        if ($percentage >= 70) return 'warning';
        return 'danger';
    }
}
