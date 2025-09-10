<?php

namespace App\Filament\Festival\Resources\Movies\Pages;

use App\Filament\Festival\Resources\Movies\MovieResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMovie extends EditRecord
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
