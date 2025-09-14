<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages;

use Modules\Fresnel\app\Filament\Resources\ValidationResults\ValidationResultResource;
use Filament\Resources\Pages\CreateRecord;

class CreateValidationResult extends CreateRecord
{
    protected static string $resource = ValidationResultResource::class;
}
