<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps\Pages;

use Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
