<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\Providers\Pages;

use Modules\Fresnel\app\Filament\Infrastructure\Resources\Providers\ProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProviders extends ManageRecords
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
