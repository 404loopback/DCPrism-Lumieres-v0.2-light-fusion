<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Pages;

use Modules\Fresnel\app\Filament\Resources\Versions\VersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
