<?php

namespace Modules\Fresnel\app\Filament\Resources\Movies\Pages;

use Modules\Fresnel\app\Filament\Resources\Movies\MovieResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovie extends CreateRecord
{
    protected static string $resource = MovieResource::class;
}
