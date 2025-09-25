<?php

namespace Modules\Fresnel\app\Filament\Resources\ActivityLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\ActivityLogResource;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pas d'actions de création pour les logs d'audit
        ];
    }
}
