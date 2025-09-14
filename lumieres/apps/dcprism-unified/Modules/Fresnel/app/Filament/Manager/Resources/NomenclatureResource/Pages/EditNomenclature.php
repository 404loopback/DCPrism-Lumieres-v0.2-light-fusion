<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource\Pages;

use Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNomenclature extends EditRecord
{
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
