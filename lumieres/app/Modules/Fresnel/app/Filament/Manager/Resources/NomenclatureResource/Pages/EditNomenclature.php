<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource;
use Modules\Fresnel\app\Models\Nomenclature;

class EditNomenclature extends EditRecord
{
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Intercepter la sauvegarde pour gérer les positions de manière sécurisée
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Vérifier si la position a changé
        $currentRecord = $this->getRecord();
        $newPosition = $data['order_position'] ?? null;
        $currentPosition = $currentRecord->order_position;
        $festivalId = $currentRecord->festival_id;

        // Si la position a changé, gérer la réorganisation
        if ($newPosition !== null && $newPosition != $currentPosition) {
            // Vérifier s'il existe déjà un enregistrement à cette position
            $existingRecord = Nomenclature::where('festival_id', $festivalId)
                ->where('order_position', $newPosition)
                ->where('id', '!=', $currentRecord->id)
                ->first();

            if ($existingRecord) {
                // Il y a un conflit, nous devons réorganiser
                DB::transaction(function () use ($currentRecord, $existingRecord, $newPosition, $currentPosition) {
                    // Swap des positions
                    // Étape 1: Mettre l'enregistrement existant en position temporaire
                    $existingRecord->update(['order_position' => -9999]);
                    
                    // Étape 2: Mettre l'enregistrement actuel à la nouvelle position
                    $currentRecord->update(['order_position' => $newPosition]);
                    
                    // Étape 3: Mettre l'enregistrement existant à l'ancienne position
                    $existingRecord->update(['order_position' => $currentPosition]);
                });

                // Retirer order_position des données à sauvegarder car on l'a déjà fait
                unset($data['order_position']);
            }
        }

        // Plus besoin de gérer le cache - le widget récupère toujours des données fraîches

        return parent::mutateFormDataBeforeSave($data);
    }
}
