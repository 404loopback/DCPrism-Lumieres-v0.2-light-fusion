<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\Dcp;
use Illuminate\Auth\Access\HandlesAuthorization;

class DcpPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Dcp');
    }

    public function view(AuthUser $authUser, Dcp $dcp): bool
    {
        return $authUser->can('View:Dcp');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Dcp');
    }

    public function update(AuthUser $authUser, Dcp $dcp): bool
    {
        return $authUser->can('Update:Dcp');
    }

    public function delete(AuthUser $authUser, Dcp $dcp): bool
    {
        return $authUser->can('Delete:Dcp');
    }

    public function restore(AuthUser $authUser, Dcp $dcp): bool
    {
        return $authUser->can('Restore:Dcp');
    }

    public function forceDelete(AuthUser $authUser, Dcp $dcp): bool
    {
        return $authUser->can('ForceDelete:Dcp');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Dcp');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Dcp');
    }

    public function replicate(AuthUser $authUser, Dcp $dcp): bool
    {
        return $authUser->can('Replicate:Dcp');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Dcp');
    }

}