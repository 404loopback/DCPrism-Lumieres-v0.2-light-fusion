<?php

namespace Modules\Fresnel\app\Filament\Festival\Resources\Movies\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\MovieResource;

class ListMovies extends ListRecords
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
