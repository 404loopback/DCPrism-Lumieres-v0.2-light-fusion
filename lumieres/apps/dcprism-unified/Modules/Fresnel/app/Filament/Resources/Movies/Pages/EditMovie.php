<?php

namespace Modules\Fresnel\app\Filament\Resources\Movies\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Movies\MovieResource;

class EditMovie extends EditRecord
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
