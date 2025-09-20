<?php

namespace Modules\Fresnel\app\Filament\Resources\Parameters\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\Parameters\ParameterResource;

class ListParameters extends ListRecords
{
    protected static string $resource = ParameterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
