<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps\Pages;

use Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
