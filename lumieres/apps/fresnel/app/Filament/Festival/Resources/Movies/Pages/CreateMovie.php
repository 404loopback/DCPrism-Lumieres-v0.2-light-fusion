<?php

namespace App\Filament\Festival\Resources\Movies\Pages;

use App\Filament\Festival\Resources\Movies\MovieResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovie extends CreateRecord
{
    protected static string $resource = MovieResource::class;
}
