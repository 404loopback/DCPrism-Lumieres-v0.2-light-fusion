<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Pages;

use Modules\Fresnel\app\Filament\Resources\Langs\LangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
