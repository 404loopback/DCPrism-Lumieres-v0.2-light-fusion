<?php

namespace Modules\Fresnel\app\Filament\Resources\Festivals\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Festivals\FestivalResource;

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
