<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\MovieMetadataResource;

class CreateMovieMetadata extends CreateRecord
{
    protected static string $resource = MovieMetadataResource::class;
}
