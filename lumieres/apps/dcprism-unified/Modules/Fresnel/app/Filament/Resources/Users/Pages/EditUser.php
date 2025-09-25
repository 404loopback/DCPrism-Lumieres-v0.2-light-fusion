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
        $formData = $this->form->getState();
        
        // Gérer les festivals assignés
        $festivalIds = $formData['festival_ids'] ?? [];
        $this->record->festivals()->sync($festivalIds);
        
        // Gérer les films assignés pour les utilisateurs Source
        if ($this->record->hasRole('source') && !empty($this->record->email)) {
            $movieIds = $formData['assigned_movie_ids'] ?? [];
            $currentEmail = $this->record->email;
            
            // Vérification de sécurité : s'assurer que l'email n'est pas null
            if (empty($currentEmail)) {
                \Log::warning('Tentative de mise à jour des films avec un email null', [
                    'user_id' => $this->record->id,
                    'form_data' => $formData,
                ]);
                return;
            }
            
            // Réinitialiser tous les films de cette source (utiliser l'ancien email)
            $originalEmail = $this->record->getOriginal('email');
            if ($originalEmail) {
                \Modules\Fresnel\app\Models\Movie::where('source_email', $originalEmail)
                    ->update(['source_email' => $currentEmail]);
            }
            
            // Assigner les nouveaux films
            if (!empty($movieIds)) {
                \Modules\Fresnel\app\Models\Movie::whereIn('id', $movieIds)
                    ->where('source_email', '!=', $currentEmail) // Éviter les doublons
                    ->update(['source_email' => $currentEmail]);
            }
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Retirer les champs qui ne sont pas des colonnes de la table users
        unset($data['festival_ids']);
        unset($data['assigned_movie_ids']);

        return $data;
    }
}
