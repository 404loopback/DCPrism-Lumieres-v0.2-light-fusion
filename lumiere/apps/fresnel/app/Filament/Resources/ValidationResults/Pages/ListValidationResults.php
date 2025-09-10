<?php

namespace App\Filament\Resources\ValidationResults\Pages;

use App\Filament\Resources\ValidationResults\ValidationResultResource;
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
