<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\ValidationResultResource;

class EditValidationResult extends EditRecord
{
    protected static string $resource = ValidationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
