<?php

namespace Modules\Fresnel\app\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\\Database\\Factories\\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Configuration du logging d'activité
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'email_verified_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Utilisateur créé',
                'updated' => 'Profil utilisateur modifié',
                'deleted' => 'Utilisateur supprimé',
                default => $eventName
            });
    }


    /**
     * Festivals assigned to the user
     */
    public function festivals(): BelongsToMany
    {
        return $this->belongsToMany(Festival::class, 'user_festivals')->withTimestamps();
    }
    
    /**
     * Détermine si l'utilisateur peut accéder à un panel Filament donné
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Vérification de base : utilisateur authentifié avec un rôle
        if (!$this->role) {
            return false;
        }

        // En environnement local, on peut bypasser la vérification d'email
        // En production, vérifier que l'email est vérifié
        if (!app()->isLocal() && !$this->email_verified_at) {
            return false;
        }

        // Mappage strict des rôles vers les panels
        $rolePanelMapping = [
            'admin' => 'admin',
            'tech' => 'tech', 
            'manager' => 'manager',
            'supervisor' => 'supervisor',
            'source' => 'source',
            'cinema' => 'cinema',
        ];

        $expectedPanel = $rolePanelMapping[$this->role] ?? null;
        
        // L'utilisateur ne peut accéder qu'à SON panel
        return $panel->getId() === $expectedPanel;
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique (version simplifiée)
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        
        return in_array($this->role, $roles);
    }
    
    /**
     * Vérifier si l'utilisateur peut accéder à un festival donné
     */
    public function canAccessFestival(int $festivalId): bool
    {
        // Seul l'admin peut accéder à tous les festivals
        if ($this->hasRole('admin')) {
            return true;
        }
        
        // Tous les autres utilisateurs (y compris superviseurs) doivent être assignés au festival
        return $this->festivals()->where('id', $festivalId)->exists();
    }
}
