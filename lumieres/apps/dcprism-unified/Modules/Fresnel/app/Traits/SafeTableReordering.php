<?php

namespace Modules\Fresnel\app\Traits;

use Illuminate\Support\Facades\Session;

trait SafeTableReordering
{
    /**
     * Override the reordering method to handle unique constraints safely
     */
    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        // Log pour déboguer
        \Log::info('SafeTableReordering::reorderTable called!', ['order' => $order, 'draggedRecordKey' => $draggedRecordKey]);

        $festivalId = Session::get('selected_festival_id');

        if (! $festivalId) {
            \Log::error('No festival selected for reordering');
            throw new \Exception('Aucun festival sélectionné. Impossible de réorganiser.');
        }

        $modelClass = static::getModel();
        \Log::info('Model class: '.$modelClass);

        // Utiliser notre méthode personnalisée pour réorganiser en sécurité
        if (method_exists($modelClass, 'reorderSafely')) {
            \Log::info('Using custom reorderSafely method', ['order' => $order, 'festival_id' => $festivalId]);
            $modelClass::reorderSafely($order, $festivalId);
        } else {
            \Log::info('Fallback to default reordering');
            // Fallback vers le comportement par défaut
            parent::reorderTable($order, $draggedRecordKey);
        }
    }
}
