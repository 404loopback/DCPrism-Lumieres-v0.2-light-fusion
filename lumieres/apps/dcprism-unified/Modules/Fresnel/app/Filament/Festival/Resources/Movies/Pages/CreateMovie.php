<?php

namespace Modules\Fresnel\app\Filament\Festival\Resources\Movies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\MovieResource;

class CreateMovie extends CreateRecord
{
    protected static string $resource = MovieResource::class;
}
