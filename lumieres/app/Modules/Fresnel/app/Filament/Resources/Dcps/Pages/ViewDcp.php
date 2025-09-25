<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource;

class ViewDcp extends ViewRecord
{
    protected static string $resource = DcpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
