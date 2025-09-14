<?php

namespace Modules\Fresnel\app\Filament\Resources\Festivals\Pages;

use Modules\Fresnel\app\Filament\Resources\Festivals\FestivalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFestival extends EditRecord
{
    protected static string $resource = FestivalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
