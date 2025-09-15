<?php

declare(strict_types=1);

namespace Modules\Fresnel\app\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Fresnel\app\Models\ValidationResult;
use Illuminate\Auth\Access\HandlesAuthorization;

class ValidationResultPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ValidationResult');
    }

    public function view(AuthUser $authUser, ValidationResult $validationResult): bool
    {
        return $authUser->can('View:ValidationResult');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ValidationResult');
    }

    public function update(AuthUser $authUser, ValidationResult $validationResult): bool
    {
        return $authUser->can('Update:ValidationResult');
    }

    public function delete(AuthUser $authUser, ValidationResult $validationResult): bool
    {
        return $authUser->can('Delete:ValidationResult');
    }

    public function restore(AuthUser $authUser, ValidationResult $validationResult): bool
    {
        return $authUser->can('Restore:ValidationResult');
    }

    public function forceDelete(AuthUser $authUser, ValidationResult $validationResult): bool
    {
        return $authUser->can('ForceDelete:ValidationResult');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ValidationResult');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ValidationResult');
    }

    public function replicate(AuthUser $authUser, ValidationResult $validationResult): bool
    {
        return $authUser->can('Replicate:ValidationResult');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ValidationResult');
    }

}