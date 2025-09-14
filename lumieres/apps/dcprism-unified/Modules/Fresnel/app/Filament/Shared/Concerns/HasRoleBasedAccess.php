<?php

namespace Modules\Fresnel\app\Filament\Shared\Concerns;

/**
 * Trait for role-based access control in Filament components
 */
trait HasRoleBasedAccess
{
    /**
     * Check if current user has any of the specified roles
     */
    protected function userHasRole(array|string $roles): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        $roles = is_array($roles) ? $roles : [$roles];
        
        return in_array($user->role, $roles);
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->userHasRole('admin');
    }
    
    /**
     * Check if user is tech
     */
    protected function isTech(): bool
    {
        return $this->userHasRole('tech');
    }
    
    /**
     * Check if user is manager
     */
    protected function isManager(): bool
    {
        return $this->userHasRole('manager');
    }
    
    /**
     * Check if user is source
     */
    protected function isSource(): bool
    {
        return $this->userHasRole('source');
    }
    
    /**
     * Check if user is cinema
     */
    protected function isCinema(): bool
    {
        return $this->userHasRole('cinema');
    }
    
    /**
     * Check if user is supervisor
     */
    protected function isSupervisor(): bool
    {
        return $this->userHasRole('supervisor');
    }

    /**
     * Check if current user has any write access
     */
    protected function hasWriteAccess(): bool
    {
        return $this->userHasRole(['admin', 'manager', 'tech']);
    }

    /**
     * Check if current user has any tech access
     */
    protected function hasTechAccess(): bool
    {
        return $this->userHasRole(['admin', 'tech']);
    }

    /**
     * Check if current user has any management access
     */
    protected function hasManagementAccess(): bool
    {
        return $this->userHasRole(['admin', 'manager', 'supervisor']);
    }

    /**
     * Check if current user has read-only access
     */
    protected function hasReadOnlyAccess(): bool
    {
        return $this->userHasRole(['supervisor', 'cinema']);
    }

    /**
     * Check if current user can upload content
     */
    protected function canUploadContent(): bool
    {
        return $this->userHasRole(['admin', 'source', 'tech', 'manager']);
    }

    /**
     * Check if current user can validate content
     */
    protected function canValidateContent(): bool
    {
        return $this->userHasRole(['admin', 'tech']);
    }

    /**
     * Check if current user can distribute content
     */
    protected function canDistributeContent(): bool
    {
        return $this->userHasRole(['admin', 'manager']);
    }

    /**
     * Get the role of current user
     */
    protected function getCurrentUserRole(): ?string
    {
        $user = auth()->user();
        
        return $user?->role;
    }
}
