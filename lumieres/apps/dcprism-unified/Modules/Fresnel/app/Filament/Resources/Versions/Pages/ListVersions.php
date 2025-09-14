<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Pages;

use Modules\Fresnel\app\Filament\Resources\Versions\VersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
