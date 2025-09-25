<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\Version;
use Illuminate\Auth\Access\HandlesAuthorization;

class VersionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Version');
    }

    public function view(AuthUser $authUser, Version $version): bool
    {
        return $authUser->can('View:Version');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Version');
    }

    public function update(AuthUser $authUser, Version $version): bool
    {
        return $authUser->can('Update:Version');
    }

    public function delete(AuthUser $authUser, Version $version): bool
    {
        return $authUser->can('Delete:Version');
    }

    public function restore(AuthUser $authUser, Version $version): bool
    {
        return $authUser->can('Restore:Version');
    }

    public function forceDelete(AuthUser $authUser, Version $version): bool
    {
        return $authUser->can('ForceDelete:Version');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Version');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Version');
    }

    public function replicate(AuthUser $authUser, Version $version): bool
    {
        return $authUser->can('Replicate:Version');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Version');
    }

}