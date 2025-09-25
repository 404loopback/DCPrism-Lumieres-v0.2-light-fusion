<?php

declare(strict_types=1);

namespace Modules\Meniscus\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Meniscus\app\Models\Job;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Job');
    }

    public function view(AuthUser $authUser, Job $job): bool
    {
        return $authUser->can('View:Job');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Job');
    }

    public function update(AuthUser $authUser, Job $job): bool
    {
        return $authUser->can('Update:Job');
    }

    public function delete(AuthUser $authUser, Job $job): bool
    {
        return $authUser->can('Delete:Job');
    }

    public function restore(AuthUser $authUser, Job $job): bool
    {
        return $authUser->can('Restore:Job');
    }

    public function forceDelete(AuthUser $authUser, Job $job): bool
    {
        return $authUser->can('ForceDelete:Job');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Job');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Job');
    }

    public function replicate(AuthUser $authUser, Job $job): bool
    {
        return $authUser->can('Replicate:Job');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Job');
    }

}