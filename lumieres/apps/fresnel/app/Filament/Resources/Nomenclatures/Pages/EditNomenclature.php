<?php

namespace App\Filament\Resources\Nomenclatures\Pages;

use App\Filament\Resources\Nomenclatures\NomenclatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNomenclature extends EditRecord
{
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
