<?php

namespace App\Filament\Resources\Langs\Pages;

use App\Filament\Resources\Langs\LangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
