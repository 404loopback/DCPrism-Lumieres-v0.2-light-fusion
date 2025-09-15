<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\Parameter;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParameterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Parameter');
    }

    public function view(AuthUser $authUser, Parameter $parameter): bool
    {
        return $authUser->can('View:Parameter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Parameter');
    }

    public function update(AuthUser $authUser, Parameter $parameter): bool
    {
        return $authUser->can('Update:Parameter');
    }

    public function delete(AuthUser $authUser, Parameter $parameter): bool
    {
        return $authUser->can('Delete:Parameter');
    }

    public function restore(AuthUser $authUser, Parameter $parameter): bool
    {
        return $authUser->can('Restore:Parameter');
    }

    public function forceDelete(AuthUser $authUser, Parameter $parameter): bool
    {
        return $authUser->can('ForceDelete:Parameter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Parameter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Parameter');
    }

    public function replicate(AuthUser $authUser, Parameter $parameter): bool
    {
        return $authUser->can('Replicate:Parameter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Parameter');
    }

}