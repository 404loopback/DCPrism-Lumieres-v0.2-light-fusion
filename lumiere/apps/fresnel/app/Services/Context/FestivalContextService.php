<?php

namespace App\Services\Context;

use App\Models\Festival;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Centralized service for managing festival context across the application.
 * Replaces scattered Session::get('selected_festival_id') calls throughout the codebase.
 */
class FestivalContextService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const SESSION_KEY = 'selected_festival_id';
    
    private ?int $currentFestivalId = null;
    private ?Festival $currentFestival = null;

    /**
     * Set the current festival for the session
     */
    public function setCurrentFestival(int $festivalId): void
    {
        $this->currentFestivalId = $festivalId;
        $this->currentFestival = null; // Reset cached festival
        
        Session::put(self::SESSION_KEY, $festivalId);
        
        Log::debug('Festival context changed', [
            'festival_id' => $festivalId,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get the current festival ID
     */
    public function getCurrentFestivalId(): ?int
    {
        if ($this->currentFestivalId === null) {
            $this->currentFestivalId = Session::get(self::SESSION_KEY);
        }
        
        return $this->currentFestivalId;
    }

    /**
     * Get the current festival model instance
     */
    public function getCurrentFestival(): ?Festival
    {
        $festivalId = $this->getCurrentFestivalId();
        
        if (!$festivalId) {
            return null;
        }
        
        if ($this->currentFestival && $this->currentFestival->id === $festivalId) {
            return $this->currentFestival;
        }
        
        $this->currentFestival = Cache::remember(
            "festival:context:{$festivalId}",
            self::CACHE_TTL,
            fn() => Festival::find($festivalId)
        );
        
        return $this->currentFestival;
    }

    /**
     * Check if a festival is currently selected
     */
    public function hasFestivalSelected(): bool
    {
        return $this->getCurrentFestivalId() !== null;
    }

    /**
     * Clear the current festival context
     */
    public function clearCurrentFestival(): void
    {
        $this->currentFestivalId = null;
        $this->currentFestival = null;
        
        Session::forget(self::SESSION_KEY);
        
        if ($festivalId = $this->getCurrentFestivalId()) {
            Cache::forget("festival:context:{$festivalId}");
        }
        
        Log::debug('Festival context cleared', [
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Ensure a festival is selected, throw exception if not
     * 
     * @throws \RuntimeException
     */
    public function requireFestivalSelected(): Festival
    {
        $festival = $this->getCurrentFestival();
        
        if (!$festival) {
            throw new \RuntimeException('No festival selected. Please select a festival first.');
        }
        
        return $festival;
    }

    /**
     * Get available festivals for the current user
     */
    public function getAvailableFestivals(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();
        
        if (!$user) {
            return collect();
        }
        
        // Cache available festivals per user
        return Cache::remember(
            "festivals:available:user:{$user->id}",
            self::CACHE_TTL,
            function () use ($user) {
                // Logic depends on user role and permissions
                if ($user->hasRole('admin')) {
                    return Festival::where('is_active', true)->get();
                }
                
                // For other roles, return festivals they have access to
                return $user->festivals()->where('is_active', true)->get();
            }
        );
    }

    /**
     * Switch to a different festival if user has access
     * 
     * @throws \UnauthorizedHttpException
     */
    public function switchToFestival(int $festivalId): bool
    {
        $availableFestivals = $this->getAvailableFestivals();
        
        if (!$availableFestivals->contains('id', $festivalId)) {
            throw new \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException(
                'Bearer',
                'You do not have access to this festival.'
            );
        }
        
        $this->setCurrentFestival($festivalId);
        return true;
    }

    /**
     * Get context information for debugging
     */
    public function getContextInfo(): array
    {
        return [
            'current_festival_id' => $this->getCurrentFestivalId(),
            'current_festival_name' => $this->getCurrentFestival()?->name,
            'has_festival_selected' => $this->hasFestivalSelected(),
            'available_festivals_count' => $this->getAvailableFestivals()->count(),
            'user_id' => auth()->id(),
            'session_id' => Session::getId(),
        ];
    }
}
