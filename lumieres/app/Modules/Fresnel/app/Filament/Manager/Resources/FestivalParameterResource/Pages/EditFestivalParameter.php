<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\FestivalParameterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Manager\Resources\FestivalParameterResource;

class EditFestivalParameter extends EditRecord
{
    protected static string $resource = FestivalParameterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->parameter->is_system),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
