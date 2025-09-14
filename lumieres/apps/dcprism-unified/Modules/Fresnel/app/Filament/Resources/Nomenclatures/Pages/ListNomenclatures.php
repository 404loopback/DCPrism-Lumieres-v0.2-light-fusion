<?php

namespace Modules\Fresnel\app\Filament\Resources\Nomenclatures\Pages;

use Modules\Fresnel\app\Filament\Resources\Nomenclatures\NomenclatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNomenclatures extends ListRecords
{
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
