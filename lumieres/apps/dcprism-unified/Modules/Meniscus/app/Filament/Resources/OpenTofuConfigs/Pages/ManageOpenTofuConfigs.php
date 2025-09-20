<?php

namespace Modules\Meniscus\app\Filament\Resources\OpenTofuConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\Meniscus\app\Filament\Resources\OpenTofuConfigs\OpenTofuConfigResource;

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
