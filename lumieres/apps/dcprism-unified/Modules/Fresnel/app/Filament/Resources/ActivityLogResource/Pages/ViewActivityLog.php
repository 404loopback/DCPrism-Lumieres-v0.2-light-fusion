<?php

namespace Modules\Fresnel\app\Filament\Resources\ActivityLogResource\Pages;

use Modules\Fresnel\app\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;

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
