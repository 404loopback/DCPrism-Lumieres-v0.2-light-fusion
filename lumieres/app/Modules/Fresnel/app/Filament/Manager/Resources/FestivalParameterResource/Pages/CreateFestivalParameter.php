<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\FestivalParameterResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Fresnel\app\Filament\Manager\Resources\FestivalParameterResource;

class CreateFestivalParameter extends CreateRecord
{
    protected static string $resource = FestivalParameterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
