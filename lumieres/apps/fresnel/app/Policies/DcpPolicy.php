<?php

namespace App\Policies;

use App\Models\Dcp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DcpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any DCPs.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'source', 'tech']);
    }

    /**
     * Determine whether the user can view the DCP.
     */
    public function view(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'manager' => $this->isDcpInUserFestivals($user, $dcp),
            'source' => $dcp->movie && $dcp->movie->source_email === $user->email,
            'tech' => true,
            default => false
        };
    }

    /**
     * Determine whether the user can create DCPs.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'source']);
    }

    /**
     * Determine whether the user can update the DCP.
     */
    public function update(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'source' => $dcp->movie && 
                       $dcp->movie->source_email === $user->email && 
                       !in_array($dcp->status, [Dcp::STATUS_VALIDATED, Dcp::STATUS_ARCHIVED]),
            'tech' => true, // Les techniciens peuvent modifier les métadonnées de validation
            default => false
        };
    }

    /**
     * Determine whether the user can delete the DCP.
     */
    public function delete(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'source' => $dcp->movie && 
                       $dcp->movie->source_email === $user->email && 
                       in_array($dcp->status, [Dcp::STATUS_UPLOADED, Dcp::STATUS_ERROR]),
            default => false
        };
    }

    /**
     * Determine whether the user can validate the DCP.
     */
    public function validate(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'tech' => in_array($dcp->status, [
                Dcp::STATUS_UPLOADED, 
                Dcp::STATUS_PROCESSING, 
                Dcp::STATUS_PENDING_VALIDATION
            ]),
            default => false
        };
    }

    /**
     * Determine whether the user can reject the DCP.
     */
    public function reject(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'tech' => in_array($dcp->status, [
                Dcp::STATUS_UPLOADED, 
                Dcp::STATUS_PROCESSING, 
                Dcp::STATUS_PENDING_VALIDATION,
                Dcp::STATUS_VALID
            ]),
            default => false
        };
    }

    /**
     * Determine whether the user can download the DCP.
     */
    public function download(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'manager' => $this->isDcpInUserFestivals($user, $dcp) && 
                        in_array($dcp->status, [Dcp::STATUS_VALIDATED, Dcp::STATUS_READY]),
            'source' => $dcp->movie && $dcp->movie->source_email === $user->email,
            'tech' => true,
            default => false
        };
    }

    /**
     * Determine whether the user can access technical metadata.
     */
    public function viewTechnicalMetadata(User $user, Dcp $dcp): bool
    {
        return in_array($user->role, ['admin', 'tech']);
    }

    /**
     * Determine whether the user can upload new versions.
     */
    public function uploadNewVersion(User $user, Dcp $dcp): bool
    {
        return match ($user->role) {
            'admin' => true,
            'source' => $dcp->movie && 
                       $dcp->movie->source_email === $user->email &&
                       in_array($dcp->status, [Dcp::STATUS_REJECTED, Dcp::STATUS_ERROR]),
            default => false
        };
    }

    /**
     * Check if DCP is in user's managed festivals
     */
    private function isDcpInUserFestivals(User $user, Dcp $dcp): bool
    {
        if ($user->role !== 'manager' || !$dcp->movie) {
            return false;
        }

        // Si l'utilisateur a une session de festival sélectionné
        $selectedFestivalId = session('selected_festival_id');
        if ($selectedFestivalId) {
            return $dcp->movie->festivals()->where('festival_id', $selectedFestivalId)->exists();
        }

        // Sinon, vérifier tous les festivals gérés par l'utilisateur
        $userFestivalIds = $user->festivals()->pluck('festival_id')->toArray();
        return $dcp->movie->festivals()->whereIn('festival_id', $userFestivalIds)->exists();
    }
}
