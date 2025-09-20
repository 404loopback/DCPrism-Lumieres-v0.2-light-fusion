<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\Versions\VersionResource;

class ListVersions extends ListRecords
{
    protected static string $resource = VersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
