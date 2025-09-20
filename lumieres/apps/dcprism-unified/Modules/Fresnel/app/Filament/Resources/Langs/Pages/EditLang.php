<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Langs\LangResource;

class EditLang extends EditRecord
{
    protected static string $resource = LangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
