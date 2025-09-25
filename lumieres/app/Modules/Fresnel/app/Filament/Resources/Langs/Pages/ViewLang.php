<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Fresnel\app\Filament\Resources\Langs\LangResource;

class ViewLang extends ViewRecord
{
    protected static string $resource = LangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
