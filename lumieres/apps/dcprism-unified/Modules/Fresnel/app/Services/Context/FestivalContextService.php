<?php

namespace Modules\Fresnel\app\Services\Context;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Fresnel\app\Models\Festival;

/**
 * Service de contexte des festivals
 * Gère le festival actuellement sélectionné par l'utilisateur
 */
class FestivalContextService
{
    protected const SESSION_FESTIVAL_ID_KEY = 'selected_festival_id';

    protected const SESSION_FESTIVAL_NAME_KEY = 'selected_festival_name';

    protected ?Festival $currentFestival = null;

    protected bool $loaded = false;

    /**
     * Obtenir le festival actuellement sélectionné
     */
    public function getCurrentFestival(): ?Festival
    {
        if (! $this->loaded) {
            $this->loadCurrentFestival();
        }

        return $this->currentFestival;
    }

    /**
     * Obtenir l'ID du festival actuellement sélectionné
     */
    public function getCurrentFestivalId(): ?int
    {
        $festival = $this->getCurrentFestival();

        return $festival?->id;
    }

    /**
     * Définir le festival actuel
     */
    public function setCurrentFestival(int|Festival $festival): self
    {
        if (is_int($festival)) {
            $festival = Festival::find($festival);
        }

        $this->currentFestival = $festival;
        $this->loaded = true;

        if ($festival) {
            session([
                self::SESSION_FESTIVAL_ID_KEY => $festival->id,
                self::SESSION_FESTIVAL_NAME_KEY => $festival->name,
            ]);
        }

        return $this;
    }

    /**
     * Effacer le festival actuel
     */
    public function clearCurrentFestival(): self
    {
        $this->currentFestival = null;
        $this->loaded = true;

        session()->forget([
            self::SESSION_FESTIVAL_ID_KEY,
            self::SESSION_FESTIVAL_NAME_KEY,
        ]);

        return $this;
    }

    /**
     * Vérifier si un festival est sélectionné
     */
    public function hasFestivalSelected(): bool
    {
        return $this->getCurrentFestival() !== null;
    }

    /**
     * Obtenir les festivals disponibles pour l'utilisateur connecté
     */
    public function getAvailableFestivals(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        // Les admins peuvent voir tous les festivals
        if ($user->hasRole('admin')) {
            return Festival::orderBy('name')->get();
        }

        // Les autres utilisateurs ne voient que leurs festivals assignés
        return $user->festivals()->orderBy('name')->get();
    }

    /**
     * Charger le festival depuis la session
     */
    protected function loadCurrentFestival(): void
    {
        $festivalId = session(self::SESSION_FESTIVAL_ID_KEY);

        if ($festivalId) {
            $this->currentFestival = Festival::find($festivalId);

            // Si le festival n'existe plus, nettoyer la session
            if (! $this->currentFestival) {
                $this->clearCurrentFestival();
            }
        }

        $this->loaded = true;
    }

    /**
     * Obtenir le nom du festival actuel
     */
    public function getCurrentFestivalName(): string
    {
        $festival = $this->getCurrentFestival();

        return $festival?->name ?? 'Aucun festival sélectionné';
    }

    /**
     * Vérifier si l'utilisateur peut accéder à un festival donné
     */
    public function canAccessFestival(int $festivalId): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->canAccessFestival($festivalId);
    }
}
