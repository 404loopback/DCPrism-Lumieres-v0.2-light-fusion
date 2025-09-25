<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Versions\VersionResource;

class EditVersion extends EditRecord
{
    protected static string $resource = VersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
