<?php

namespace Modules\Fresnel\app\Filament\Resources\Nomenclatures\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\NomenclatureResource;
use Modules\Fresnel\app\Models\Nomenclature;

class EditNomenclature extends EditRecord
{
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Méthode simplifiée pour gérer les positions avant sauvegarde
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $newPosition = $data['order_position'] ?? null;
        $currentPosition = $record->order_position;
        $festivalId = $record->festival_id;

        // Si la position a changé, gérer les conflits AVANT la sauvegarde
        if ($newPosition !== null && $newPosition != $currentPosition) {
            $existingRecord = Nomenclature::where('festival_id', $festivalId)
                ->where('order_position', $newPosition)
                ->where('id', '!=', $record->id)
                ->first();

            if ($existingRecord) {
                // Il y a conflit : faire un swap des positions
                $existingRecord->update(['order_position' => $currentPosition]);
            }
        }

        // Invalider le cache du widget d'aperçu après toute modification
        $festivalId = Session::get('selected_festival_id');
        if ($festivalId) {
            Cache::forget("nomenclature_preview_{$festivalId}");
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}
