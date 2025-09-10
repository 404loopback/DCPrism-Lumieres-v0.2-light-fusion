<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function afterCreate(): void
    {
        $festivalIds = $this->form->getState()['festival_ids'] ?? [];
        
        if (!empty($festivalIds)) {
            $this->record->festivals()->sync($festivalIds);
        }
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Retirer festival_ids des données avant création pour éviter l'erreur de colonne
        unset($data['festival_ids']);
        return $data;
    }
}
