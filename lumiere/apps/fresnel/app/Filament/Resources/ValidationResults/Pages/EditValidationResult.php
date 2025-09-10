<?php

namespace App\Filament\Resources\ValidationResults\Pages;

use App\Filament\Resources\ValidationResults\ValidationResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
