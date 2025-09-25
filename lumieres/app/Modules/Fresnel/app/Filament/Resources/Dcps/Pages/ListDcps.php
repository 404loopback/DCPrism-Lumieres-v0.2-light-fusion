<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource;

class ListDcps extends ListRecords
{
    protected static string $resource = DcpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
