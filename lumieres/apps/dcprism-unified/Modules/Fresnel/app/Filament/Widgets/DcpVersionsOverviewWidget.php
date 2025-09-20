<?php

namespace Modules\Fresnel\app\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Models\Lang;
use Modules\Fresnel\app\Models\Version;

class DcpVersionsOverviewWidget extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total DCPs', Dcp::count())
                ->description('Fichiers DCP dans le système')
                ->descriptionIcon('heroicon-m-film')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Stat::make('DCPs Validés', Dcp::where('is_valid', true)->count())
                ->description('DCPs prêts pour diffusion')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([4, 1, 6, 2, 8, 3, 12])
                ->color('success'),

            Stat::make('En Attente', Dcp::where('status', Dcp::STATUS_UPLOADED)->count())
                ->description('DCPs en attente de validation')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([3, 1, 4, 1, 7, 1, 5])
                ->color('warning'),

            Stat::make('Versions Linguistiques', Version::count())
                ->description('Total des versions créées')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->chart([2, 4, 3, 6, 5, 8, 7])
                ->color('info'),

            Stat::make('Langues Configurées', Lang::count())
                ->description('Langues disponibles dans le système')
                ->descriptionIcon('heroicon-m-language')
                ->color('gray'),

            Stat::make('Taille Totale', $this->getTotalSize())
                ->description('Espace utilisé par les DCPs')
                ->descriptionIcon('heroicon-m-server')
                ->color('primary'),
        ];
    }

    private function getTotalSize(): string
    {
        $totalBytes = Dcp::sum('file_size') ?? 0;

        if ($totalBytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $totalBytes;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function getColumns(): int
    {
        return 3; // 3 colonnes sur desktop
    }

    protected static ?int $sort = 1; // Afficher en premier sur le dashboard
}
