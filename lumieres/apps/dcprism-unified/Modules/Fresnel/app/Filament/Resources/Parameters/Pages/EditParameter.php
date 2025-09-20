<?php

namespace Modules\Fresnel\app\Filament\Resources\Parameters\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Parameters\ParameterResource;

class EditParameter extends EditRecord
{
    protected static string $resource = ParameterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
