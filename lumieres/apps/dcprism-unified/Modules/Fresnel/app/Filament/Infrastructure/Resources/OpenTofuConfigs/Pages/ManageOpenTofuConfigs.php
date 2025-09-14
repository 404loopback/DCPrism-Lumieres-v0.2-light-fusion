<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\OpenTofuConfigs\Pages;

use Modules\Fresnel\app\Filament\Infrastructure\Resources\OpenTofuConfigs\OpenTofuConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOpenTofuConfigs extends ManageRecords
{
    protected static string $resource = OpenTofuConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
