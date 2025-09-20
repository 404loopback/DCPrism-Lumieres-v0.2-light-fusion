<?php

namespace Modules\Fresnel\app\Filament\Resources\Users\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Fresnel\app\Filament\Resources\Users\UserResource;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $festivalIds = $this->form->getState()['festival_ids'] ?? [];
        $this->record->festivals()->sync($festivalIds);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Retirer festival_ids des données avant sauvegarde pour éviter l'erreur de colonne
        unset($data['festival_ids']);

        return $data;
    }
}
