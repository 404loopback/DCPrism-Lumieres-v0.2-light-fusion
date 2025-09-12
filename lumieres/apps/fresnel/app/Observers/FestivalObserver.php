<?php

namespace App\Observers;

use App\Models\Festival;
use App\Models\Parameter;
use App\Models\FestivalParameter;
use Illuminate\Support\Facades\Log;

class FestivalObserver
{
    /**
     * Handle the Festival "created" event.
     */
    public function created(Festival $festival): void
    {
        $this->assignSystemParameters($festival);
    }

    /**
     * Handle the Festival "updated" event.
     */
    public function updated(Festival $festival): void
    {
        // Si le festival devient actif, s'assurer que les paramètres système sont bien assignés
        if ($festival->wasChanged('is_active') && $festival->is_active) {
            $this->assignSystemParameters($festival);
        }
    }

    /**
     * Assigner automatiquement tous les paramètres système au festival
     */
    private function assignSystemParameters(Festival $festival): void
    {
        try {
            // Récupérer tous les paramètres système actifs
            $systemParameters = Parameter::where('is_system', true)
                ->active()
                ->get();

            foreach ($systemParameters as $parameter) {
                // Vérifier si le paramètre n'est pas déjà assigné
                $exists = FestivalParameter::where('festival_id', $festival->id)
                    ->where('parameter_id', $parameter->id)
                    ->exists();

                if (!$exists) {
                    FestivalParameter::create([
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                        'is_enabled' => true,
                        'display_order' => -1,
                        'festival_specific_notes' => 'Paramètre système assigné automatiquement',
                    ]);

                    Log::info("Paramètre système '{$parameter->name}' assigné automatiquement au festival '{$festival->name}'");
                }
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'assignation des paramètres système au festival {$festival->id}: " . $e->getMessage());
        }
    }
}
