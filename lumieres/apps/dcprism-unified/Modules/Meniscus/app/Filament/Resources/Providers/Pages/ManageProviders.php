<?php

namespace Modules\Meniscus\app\Filament\Resources\Providers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\Meniscus\app\Filament\Resources\Providers\ProviderResource;

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
