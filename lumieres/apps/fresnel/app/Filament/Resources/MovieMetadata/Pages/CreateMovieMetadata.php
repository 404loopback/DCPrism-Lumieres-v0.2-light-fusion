<?php

namespace App\Filament\Resources\MovieMetadata\Pages;

use App\Filament\Resources\MovieMetadata\MovieMetadataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovieMetadata extends CreateRecord
{
    protected static string $resource = MovieMetadataResource::class;
}
