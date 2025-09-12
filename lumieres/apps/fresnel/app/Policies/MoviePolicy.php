<?php

namespace App\Policies;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MoviePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any movies.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'source', 'tech']);
    }

    /**
     * Determine whether the user can view the movie.
     */
    public function view(User $user, Movie $movie): bool
    {
        return match ($user->role) {
            'admin' => true,
            'manager' => $this->isMovieInUserFestivals($user, $movie),
            'source' => $movie->source_email === $user->email,
            'tech' => true,
            default => false
        };
    }

    /**
     * Determine whether the user can create movies.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'source']);
    }

    /**
     * Determine whether the user can update the movie.
     */
    public function update(User $user, Movie $movie): bool
    {
        return match ($user->role) {
            'admin' => true,
            'manager' => $this->isMovieInUserFestivals($user, $movie),
            'source' => $movie->source_email === $user->email && 
                       !in_array($movie->status, [Movie::STATUS_VALIDATION_OK, Movie::STATUS_DISTRIBUTION_OK]),
            default => false
        };
    }

    /**
     * Determine whether the user can delete the movie.
     */
    public function delete(User $user, Movie $movie): bool
    {
        return match ($user->role) {
            'admin' => true,
            'manager' => $this->isMovieInUserFestivals($user, $movie) && 
                        !in_array($movie->status, [Movie::STATUS_VALIDATION_OK, Movie::STATUS_DISTRIBUTION_OK]),
            default => false
        };
    }

    /**
     * Determine whether the user can restore the movie.
     */
    public function restore(User $user, Movie $movie): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the movie.
     */
    public function forceDelete(User $user, Movie $movie): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can validate the movie technically.
     */
    public function technicalValidate(User $user, Movie $movie): bool
    {
        return $user->role === 'tech' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can upload DCPs for this movie.
     */
    public function uploadDcp(User $user, Movie $movie): bool
    {
        return match ($user->role) {
            'admin' => true,
            'source' => $movie->source_email === $user->email,
            default => false
        };
    }

    /**
     * Determine whether the user can manage festival assignment.
     */
    public function manageFestivalAssignment(User $user, Movie $movie): bool
    {
        return match ($user->role) {
            'admin' => true,
            'manager' => $this->isMovieInUserFestivals($user, $movie),
            default => false
        };
    }

    /**
     * Check if movie is in user's managed festivals
     */
    private function isMovieInUserFestivals(User $user, Movie $movie): bool
    {
        if ($user->role !== 'manager') {
            return false;
        }

        // Si l'utilisateur a une session de festival sélectionné
        $selectedFestivalId = session('selected_festival_id');
        if ($selectedFestivalId) {
            return $movie->festivals()->where('festival_id', $selectedFestivalId)->exists();
        }

        // Sinon, vérifier tous les festivals gérés par l'utilisateur
        $userFestivalIds = $user->festivals()->pluck('festival_id')->toArray();
        return $movie->festivals()->whereIn('festival_id', $userFestivalIds)->exists();
    }
}
