<?php

namespace App\Filament\Resources\ValidationResults\Pages;

use App\Filament\Resources\ValidationResults\ValidationResultResource;
use Filament\Resources\Pages\CreateRecord;

class CreateValidationResult extends CreateRecord
{
    protected static string $resource = ValidationResultResource::class;
}
