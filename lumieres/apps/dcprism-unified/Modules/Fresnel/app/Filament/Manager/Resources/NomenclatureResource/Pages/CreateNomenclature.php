<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource;
use Modules\Fresnel\app\Models\Nomenclature;

class CreateNomenclature extends CreateRecord
{
    protected static string $resource = NomenclatureResource::class;

    /**
     * Intercepter la création pour gérer les positions de manière sécurisée
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $newPosition = $data['order_position'] ?? null;
        $festivalId = $data['festival_id'] ?? null;

        if ($newPosition !== null && $festivalId !== null) {
            // Vérifier s'il existe déjà un enregistrement à cette position
            $existingRecord = Nomenclature::where('festival_id', $festivalId)
                ->where('order_position', $newPosition)
                ->first();

            if ($existingRecord) {
                // Il y a un conflit, décaler tous les enregistrements suivants
                DB::transaction(function () use ($festivalId, $newPosition) {
                    // Décaler toutes les positions égales ou supérieures
                    Nomenclature::where('festival_id', $festivalId)
                        ->where('order_position', '>=', $newPosition)
                        ->increment('order_position');
                });
            }
        }

        // Invalider le cache du widget d'aperçu après création
        $festivalId = Session::get('selected_festival_id');
        if ($festivalId) {
            Cache::forget("nomenclature_preview_{$festivalId}");
        }

        return parent::mutateFormDataBeforeCreate($data);
    }
}
