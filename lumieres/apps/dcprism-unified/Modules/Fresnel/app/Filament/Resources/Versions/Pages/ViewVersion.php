<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Fresnel\app\Filament\Resources\Versions\VersionResource;

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
