<?php

declare(strict_types=1);

namespace Modules\Meniscus\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Meniscus\app\Models\InfrastructureDeployment;
use Illuminate\Auth\Access\HandlesAuthorization;

class InfrastructureDeploymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InfrastructureDeployment');
    }

    public function view(AuthUser $authUser, InfrastructureDeployment $infrastructureDeployment): bool
    {
        return $authUser->can('View:InfrastructureDeployment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InfrastructureDeployment');
    }

    public function update(AuthUser $authUser, InfrastructureDeployment $infrastructureDeployment): bool
    {
        return $authUser->can('Update:InfrastructureDeployment');
    }

    public function delete(AuthUser $authUser, InfrastructureDeployment $infrastructureDeployment): bool
    {
        return $authUser->can('Delete:InfrastructureDeployment');
    }

    public function restore(AuthUser $authUser, InfrastructureDeployment $infrastructureDeployment): bool
    {
        return $authUser->can('Restore:InfrastructureDeployment');
    }

    public function forceDelete(AuthUser $authUser, InfrastructureDeployment $infrastructureDeployment): bool
    {
        return $authUser->can('ForceDelete:InfrastructureDeployment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InfrastructureDeployment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InfrastructureDeployment');
    }

    public function replicate(AuthUser $authUser, InfrastructureDeployment $infrastructureDeployment): bool
    {
        return $authUser->can('Replicate:InfrastructureDeployment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InfrastructureDeployment');
    }

}