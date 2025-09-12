<?php

namespace App\Filament\Manager\Resources\FestivalParameterResource\Pages;

use App\Filament\Manager\Resources\FestivalParameterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFestivalParameter extends EditRecord
{
    protected static string $resource = FestivalParameterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => !$this->getRecord()->parameter->is_system),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
