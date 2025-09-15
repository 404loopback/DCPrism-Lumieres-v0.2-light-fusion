<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\MovieMetadata;
use Illuminate\Auth\Access\HandlesAuthorization;

class MovieMetadataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MovieMetadata');
    }

    public function view(AuthUser $authUser, MovieMetadata $movieMetadata): bool
    {
        return $authUser->can('View:MovieMetadata');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MovieMetadata');
    }

    public function update(AuthUser $authUser, MovieMetadata $movieMetadata): bool
    {
        return $authUser->can('Update:MovieMetadata');
    }

    public function delete(AuthUser $authUser, MovieMetadata $movieMetadata): bool
    {
        return $authUser->can('Delete:MovieMetadata');
    }

    public function restore(AuthUser $authUser, MovieMetadata $movieMetadata): bool
    {
        return $authUser->can('Restore:MovieMetadata');
    }

    public function forceDelete(AuthUser $authUser, MovieMetadata $movieMetadata): bool
    {
        return $authUser->can('ForceDelete:MovieMetadata');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MovieMetadata');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MovieMetadata');
    }

    public function replicate(AuthUser $authUser, MovieMetadata $movieMetadata): bool
    {
        return $authUser->can('Replicate:MovieMetadata');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MovieMetadata');
    }

}