<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages;

use Modules\Fresnel\app\Filament\Resources\MovieMetadata\MovieMetadataResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMovieMetadata extends EditRecord
{
    protected static string $resource = MovieMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
