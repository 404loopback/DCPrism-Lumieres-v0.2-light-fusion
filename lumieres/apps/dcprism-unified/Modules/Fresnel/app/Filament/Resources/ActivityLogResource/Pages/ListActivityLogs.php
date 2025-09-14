<?php

namespace Modules\Fresnel\app\Filament\Resources\ActivityLogResource\Pages;

use Modules\Fresnel\app\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

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
