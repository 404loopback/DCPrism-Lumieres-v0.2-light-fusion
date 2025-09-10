<?php

namespace App\Filament\Resources\Langs\Pages;

use App\Filament\Resources\Langs\LangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
