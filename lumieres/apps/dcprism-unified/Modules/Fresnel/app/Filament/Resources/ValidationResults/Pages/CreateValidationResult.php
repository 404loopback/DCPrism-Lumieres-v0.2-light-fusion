<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\ValidationResultResource;

class CreateValidationResult extends CreateRecord
{
    protected static string $resource = ValidationResultResource::class;
}
