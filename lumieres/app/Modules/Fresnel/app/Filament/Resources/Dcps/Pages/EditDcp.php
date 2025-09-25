<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Dcps\DcpResource;

class EditDcp extends EditRecord
{
    protected static string $resource = DcpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
