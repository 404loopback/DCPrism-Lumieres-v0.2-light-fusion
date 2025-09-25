<?php

namespace Modules\Fresnel\app\Filament\Resources\Nomenclatures\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\NomenclatureResource;
use Modules\Fresnel\app\Traits\SafeTableReordering;

class ListNomenclatures extends ListRecords
{
    use SafeTableReordering;
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
