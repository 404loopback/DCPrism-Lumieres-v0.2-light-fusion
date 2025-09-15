<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\Lang;
use Illuminate\Auth\Access\HandlesAuthorization;

class LangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lang');
    }

    public function view(AuthUser $authUser, Lang $lang): bool
    {
        return $authUser->can('View:Lang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lang');
    }

    public function update(AuthUser $authUser, Lang $lang): bool
    {
        return $authUser->can('Update:Lang');
    }

    public function delete(AuthUser $authUser, Lang $lang): bool
    {
        return $authUser->can('Delete:Lang');
    }

    public function restore(AuthUser $authUser, Lang $lang): bool
    {
        return $authUser->can('Restore:Lang');
    }

    public function forceDelete(AuthUser $authUser, Lang $lang): bool
    {
        return $authUser->can('ForceDelete:Lang');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lang');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lang');
    }

    public function replicate(AuthUser $authUser, Lang $lang): bool
    {
        return $authUser->can('Replicate:Lang');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lang');
    }

}