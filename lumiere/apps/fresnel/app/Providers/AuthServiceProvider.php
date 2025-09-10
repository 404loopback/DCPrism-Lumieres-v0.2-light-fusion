<?php

namespace App\Providers;

use App\Models\Movie;
use App\Models\Dcp;
use App\Models\User;
use App\Models\Festival;
use App\Models\Version;
use App\Policies\MoviePolicy;
use App\Policies\DcpPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Movie::class => MoviePolicy::class,
        Dcp::class => DcpPolicy::class,
        // Version::class => VersionPolicy::class, // À créer si nécessaire
        // Festival::class => FestivalPolicy::class, // À créer si nécessaire
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates personnalisées pour les actions spécifiques
        
        // Gate pour l'accès aux panels Filament selon le rôle
        Gate::define('access-admin-panel', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('access-manager-panel', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('access-source-panel', function (User $user) {
            return in_array($user->role, ['admin', 'source']);
        });

        Gate::define('access-tech-panel', function (User $user) {
            return in_array($user->role, ['admin', 'tech']);
        });

        Gate::define('access-cinema-panel', function (User $user) {
            return in_array($user->role, ['admin', 'cinema']);
        });

        Gate::define('access-supervisor-panel', function (User $user) {
            return in_array($user->role, ['admin', 'supervisor']);
        });

        // Gates pour les actions système spécifiques
        
        Gate::define('manage-users', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-festivals', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('manage-nomenclatures', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-parameters', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('view-system-logs', function (User $user) {
            return in_array($user->role, ['admin', 'tech']);
        });

        Gate::define('manage-job-queues', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('access-api', function (User $user) {
            return in_array($user->role, ['admin', 'source', 'tech']);
        });

        // Gates pour les actions DCP spécifiques
        
        Gate::define('validate-dcps', function (User $user) {
            return in_array($user->role, ['admin', 'tech']);
        });

        Gate::define('upload-dcps', function (User $user) {
            return in_array($user->role, ['admin', 'source']);
        });

        Gate::define('download-dcps', function (User $user) {
            return in_array($user->role, ['admin', 'manager', 'source', 'tech']);
        });

        Gate::define('delete-dcps', function (User $user) {
            return in_array($user->role, ['admin', 'source']);
        });

        // Gates pour les festivals (context-aware)
        
        Gate::define('manage-festival-movies', function (User $user, Festival $festival) {
            if ($user->role === 'admin') {
                return true;
            }

            if ($user->role === 'manager') {
                // Vérifier si l'utilisateur a accès à ce festival
                return $user->festivals()->where('festival_id', $festival->id)->exists() ||
                       session('selected_festival_id') == $festival->id;
            }

            return false;
        });

        Gate::define('view-festival-stats', function (User $user, Festival $festival) {
            if ($user->role === 'admin') {
                return true;
            }

            if ($user->role === 'manager') {
                return $user->festivals()->where('festival_id', $festival->id)->exists() ||
                       session('selected_festival_id') == $festival->id;
            }

            return false;
        });

        // Gates pour les actions de validation technique
        
        Gate::define('bulk-validate-dcps', function (User $user) {
            return in_array($user->role, ['admin', 'tech']);
        });

        Gate::define('bulk-reject-dcps', function (User $user) {
            return in_array($user->role, ['admin', 'tech']);
        });

        Gate::define('override-validation', function (User $user) {
            return $user->role === 'admin';
        });

        // Gates pour l'observabilité et le monitoring
        
        Gate::define('view-monitoring-dashboard', function (User $user) {
            return in_array($user->role, ['admin', 'tech']);
        });

        Gate::define('manage-storage', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('cleanup-failed-uploads', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
