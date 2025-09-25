<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\Nomenclature;
use Illuminate\Auth\Access\HandlesAuthorization;

class NomenclaturePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Nomenclature');
    }

    public function view(AuthUser $authUser, Nomenclature $nomenclature): bool
    {
        return $authUser->can('View:Nomenclature');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Nomenclature');
    }

    public function update(AuthUser $authUser, Nomenclature $nomenclature): bool
    {
        return $authUser->can('Update:Nomenclature');
    }

    public function delete(AuthUser $authUser, Nomenclature $nomenclature): bool
    {
        return $authUser->can('Delete:Nomenclature');
    }

    public function restore(AuthUser $authUser, Nomenclature $nomenclature): bool
    {
        return $authUser->can('Restore:Nomenclature');
    }

    public function forceDelete(AuthUser $authUser, Nomenclature $nomenclature): bool
    {
        return $authUser->can('ForceDelete:Nomenclature');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Nomenclature');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Nomenclature');
    }

    public function replicate(AuthUser $authUser, Nomenclature $nomenclature): bool
    {
        return $authUser->can('Replicate:Nomenclature');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Nomenclature');
    }

}