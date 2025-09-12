<?php

namespace App\Filament\Resources\MovieMetadata\Pages;

use App\Filament\Resources\MovieMetadata\MovieMetadataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMovieMetadata extends ListRecords
{
    protected static string $resource = MovieMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
