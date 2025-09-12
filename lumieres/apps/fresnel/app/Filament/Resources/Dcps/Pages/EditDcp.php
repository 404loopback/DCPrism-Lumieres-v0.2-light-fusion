<?php

namespace App\Filament\Resources\Dcps\Pages;

use App\Filament\Resources\Dcps\DcpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
