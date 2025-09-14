<?php

namespace Modules\Fresnel\app\Filament\Resources\Movies\Pages;

use Modules\Fresnel\app\Filament\Resources\Movies\MovieResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMovie extends ViewRecord
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
