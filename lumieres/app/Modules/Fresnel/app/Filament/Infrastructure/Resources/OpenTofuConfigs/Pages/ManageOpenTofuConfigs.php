<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\OpenTofuConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\Fresnel\app\Filament\Infrastructure\Resources\OpenTofuConfigs\OpenTofuConfigResource;

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
