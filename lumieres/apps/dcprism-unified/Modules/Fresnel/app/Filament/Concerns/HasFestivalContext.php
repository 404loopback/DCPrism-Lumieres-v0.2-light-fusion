<?php

namespace Modules\Fresnel\app\Filament\Concerns;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Modules\Fresnel\app\Models\Festival;

trait HasFestivalContext
{
    /**
     * Écoute les changements de festival
     */
    #[On('festival-changed')]
    public function refreshOnFestivalChange(): void
    {
        // Force le rafraîchissement du widget
        $this->resetStatsCacheProperties();
    }

    /**
     * Obtient l'ID du festival actuellement sélectionné
     */
    protected function getSelectedFestivalId(): ?int
    {
        return session('selected_festival_id');
    }

    /**
     * Obtient le festival actuellement sélectionné
     */
    protected function getSelectedFestival(): ?Festival
    {
        $festivalId = $this->getSelectedFestivalId();

        return $festivalId ? Festival::find($festivalId) : null;
    }

    /**
     * Vérifie si un festival est sélectionné
     */
    protected function hasFestivalSelected(): bool
    {
        return $this->getSelectedFestivalId() !== null;
    }

    /**
     * Obtient la liste des festivals disponibles pour l'utilisateur
     */
    protected function getAvailableFestivals()
    {
        $user = Auth::user();

        if (! $user) {
            return collect([]);
        }

        // Si admin, tous les festivals
        if ($user->hasRole('admin')) {
            return Festival::active()->orderBy('name')->get();
        }

        // Sinon, seulement les festivals assignés
        return $user->festivals()->active()->orderBy('name')->get();
    }

    /**
     * Applique un filtre de festival à une query donnée
     * Utilise la relation movies.festivals pour filtrer
     */
    protected function scopeBySelectedFestival($query)
    {
        $festivalId = $this->getSelectedFestivalId();

        if (! $festivalId) {
            // Si aucun festival sélectionné, retourner une query vide
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('movie.festivals', function ($q) use ($festivalId) {
            $q->where('festivals.id', $festivalId);
        });
    }

    /**
     * Reset les propriétés en cache pour forcer le recalcul
     * Cette méthode peut être surchargée dans chaque widget selon ses besoins
     */
    protected function resetStatsCacheProperties(): void
    {
        // Méthode de base - peut être surchargée
        if (property_exists($this, 'cachedStats')) {
            $this->cachedStats = null;
        }
    }
}
