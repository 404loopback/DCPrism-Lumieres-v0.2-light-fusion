<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Modules\Fresnel\app\Models\Upload;
use Modules\Fresnel\app\Models\Movie;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UploadTrendsWidget extends ChartWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 2;
    
    protected ?string $heading = 'Tendances d\'upload - 7 derniers jours';
    protected ?string $description = 'Volume des uploads DCP jour par jour';

    protected function getData(): array
    {
        $days = collect();
        $uploads = collect();
        $completedUploads = collect();
        
        // Générer les 7 derniers jours
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            $days->push($date->format('d/m'));
            
            // Compter les uploads du jour
            $dailyUploads = Upload::whereDate('created_at', $date)->count();
            $uploads->push($dailyUploads);
            
            // Compter les uploads complétés du jour
            $dailyCompleted = Upload::whereDate('updated_at', $date)
                ->where('status', 'completed')
                ->count();
            $completedUploads->push($dailyCompleted);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Uploads initiés',
                    'data' => $uploads->toArray(),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Uploads terminés',
                    'data' => $completedUploads->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
