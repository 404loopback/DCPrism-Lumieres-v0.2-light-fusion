<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\MovieMetadataResource;

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
