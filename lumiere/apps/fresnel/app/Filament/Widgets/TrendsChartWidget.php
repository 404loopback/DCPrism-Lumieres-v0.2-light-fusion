<?php

namespace App\Filament\Widgets;

use App\Models\Movie;
use App\Models\Upload;
use App\Models\ValidationResult;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendsChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 2;
    
    protected ?string $heading = 'Répartition des statuts DCP';
    protected ?string $description = 'Vue d’ensemble de l’état des DCP dans le système';
    
    // protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statusCounts = Movie::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
            
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        foreach ($statusCounts as $status) {
            switch ($status->status) {
                case 'draft':
                    $labels[] = 'Brouillon';
                    $backgroundColor[] = '#9CA3AF';
                    break;
                case 'processing':
                    $labels[] = 'En traitement';
                    $backgroundColor[] = '#F59E0B';
                    break;
                case 'ready':
                    $labels[] = 'Prêt';
                    $backgroundColor[] = '#3B82F6';
                    break;
                case 'validated':
                    $labels[] = 'Validé';
                    $backgroundColor[] = '#10B981';
                    break;
                case 'failed':
                    $labels[] = 'Échoué';
                    $backgroundColor[] = '#EF4444';
                    break;
                default:
                    $labels[] = ucfirst($status->status);
                    $backgroundColor[] = '#6B7280';
            }
            $data[] = $status->count;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'DCP par statut',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
