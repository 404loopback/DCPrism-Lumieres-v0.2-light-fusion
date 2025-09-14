<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Pages;

use Modules\Fresnel\app\Filament\Resources\Versions\VersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVersion extends ViewRecord
{
    protected static string $resource = VersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
