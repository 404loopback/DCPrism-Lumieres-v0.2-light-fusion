<?php

namespace Modules\Fresnel\app\Filament\Resources\ActivityLogResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Fresnel\app\Filament\Resources\ActivityLogResource;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pas d'actions d'édition pour les logs d'audit
        ];
    }
}
