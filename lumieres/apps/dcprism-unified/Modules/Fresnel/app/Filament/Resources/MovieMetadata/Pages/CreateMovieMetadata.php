<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages;

use Modules\Fresnel\app\Filament\Resources\MovieMetadata\MovieMetadataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovieMetadata extends CreateRecord
{
    protected static string $resource = MovieMetadataResource::class;
}
