<?php

namespace App\Filament\Festival\Resources\Movies\Pages;

use App\Filament\Festival\Resources\Movies\MovieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

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
