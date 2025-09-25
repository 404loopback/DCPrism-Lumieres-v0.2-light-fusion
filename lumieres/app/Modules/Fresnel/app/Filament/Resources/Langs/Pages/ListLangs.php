<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\Langs\LangResource;

class ListLangs extends ListRecords
{
    protected static string $resource = LangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
