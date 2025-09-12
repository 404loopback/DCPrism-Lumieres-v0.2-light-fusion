<?php

namespace App\Filament\Resources\Versions\Pages;

use App\Filament\Resources\Versions\VersionResource;
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
