<?php

namespace Modules\Fresnel\app\Filament\Tech\Widgets;

use Modules\Fresnel\app\Models\Dcp;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TechnicalValidationWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingValidation = Dcp::where('status', 'pending_validation')->count();
        $validatedDcps = Dcp::where('status', 'validated')->count();
        $failedValidation = Dcp::where('status', 'validation_failed')->count();
        $processingQueue = Dcp::where('status', 'processing')->count();
        
        return [
            Stat::make('En Attente Validation', $pendingValidation)
                ->description('DCP à valider')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Validés', $validatedDcps)
                ->description('Validation technique OK')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Échecs', $failedValidation)
                ->description('Validation échouée')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
                
            Stat::make('En Traitement', $processingQueue)
                ->description('File de traitement')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('info'),
        ];
    }
}
