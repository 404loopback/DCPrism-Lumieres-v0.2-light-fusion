<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Modules\Fresnel\app\Models\Upload;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Services\BackblazeService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StorageUsageWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 2;
    
    protected function getStats(): array
    {
        return [
            Stat::make('Espace total utilisé', $this->formatBytes($this->getTotalStorageUsed()))
                ->description('Stockage Backblaze B2 utilisé')
                ->descriptionIcon('heroicon-o-cloud-arrow-up')
                ->color('primary')
                ->chart([15, 25, 40, 35, 55, 45, 65, 70, 85, 90, 95, 100]),
                
            Stat::make('Fichiers stockés', Upload::where('status', 'completed')->count())
                ->description('DCP stockés dans le cloud')
                ->descriptionIcon('heroicon-o-document-duplicate')
                ->color('success')
                ->chart([10, 15, 20, 18, 25, 30, 28, 35, 40, 38, 45, 50]),
                
            Stat::make('Uploads en cours', Upload::where('status', 'uploading')->count())
                ->description('Transfers en cours')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('warning')
                ->chart([2, 1, 3, 2, 4, 1, 2, 3, 1, 2, 3, 1]),
                
            Stat::make('Quota restant', $this->getQuotaRemaining() . '%')
                ->description('% de quota disponible')
                ->descriptionIcon('heroicon-o-scale')
                ->color($this->getQuotaRemaining() > 20 ? 'success' : ($this->getQuotaRemaining() > 10 ? 'warning' : 'danger'))
                ->chart([100, 95, 90, 85, 80, 75, 70, 65, 60, 55, 50, 45]),
        ];
    }
    
    private function getTotalStorageUsed(): int
    {
        return Upload::where('status', 'completed')
            ->sum('file_size') ?? 0;
    }
    
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    private function getQuotaRemaining(): int
    {
        // Simuler un quota de 1TB pour l'exemple
        $maxQuota = 1024 * 1024 * 1024 * 1024; // 1TB en bytes
        $used = $this->getTotalStorageUsed();
        
        if ($maxQuota === 0) return 0;
        
        $remaining = (($maxQuota - $used) / $maxQuota) * 100;
        return max(0, min(100, (int) round($remaining)));
    }
}
