<?php

declare(strict_types=1);

namespace Modules\Meniscus\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Meniscus\app\Models\OpenTofuConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpenTofuConfigPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OpenTofuConfig');
    }

    public function view(AuthUser $authUser, OpenTofuConfig $openTofuConfig): bool
    {
        return $authUser->can('View:OpenTofuConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OpenTofuConfig');
    }

    public function update(AuthUser $authUser, OpenTofuConfig $openTofuConfig): bool
    {
        return $authUser->can('Update:OpenTofuConfig');
    }

    public function delete(AuthUser $authUser, OpenTofuConfig $openTofuConfig): bool
    {
        return $authUser->can('Delete:OpenTofuConfig');
    }

    public function restore(AuthUser $authUser, OpenTofuConfig $openTofuConfig): bool
    {
        return $authUser->can('Restore:OpenTofuConfig');
    }

    public function forceDelete(AuthUser $authUser, OpenTofuConfig $openTofuConfig): bool
    {
        return $authUser->can('ForceDelete:OpenTofuConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OpenTofuConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OpenTofuConfig');
    }

    public function replicate(AuthUser $authUser, OpenTofuConfig $openTofuConfig): bool
    {
        return $authUser->can('Replicate:OpenTofuConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OpenTofuConfig');
    }

}