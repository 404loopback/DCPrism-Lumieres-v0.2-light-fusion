<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages;

use Modules\Fresnel\app\Filament\Resources\ValidationResults\ValidationResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListValidationResults extends ListRecords
{
    protected static string $resource = ValidationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
