<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Modules\Fresnel\app\Models\User;

/**
 * Seeder pour la configuration complète des rôles Shield
 * - Crée le rôle super_admin
 * - Assigne les permissions panel à chaque rôle
 * - Assigne les rôles aux utilisateurs
 * - Configure l'utilisateur admin avec super_admin
 */
class ShieldRoleAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Réinitialiser le cache des permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🛡️  Configuration complète des rôles Shield...');
        $this->command->info('');

        // 1. Créer et configurer le rôle super_admin
        $this->createSuperAdminRole();

        // 2. Assigner les permissions panel aux rôles existants
        $this->assignPanelPermissions();

        // 3. Assigner les rôles aux utilisateurs
        $this->assignRolesToUsers();

        $this->command->info('');
        $this->command->info('✅ Configuration des rôles Shield terminée !');
        $this->showRolesSummary();
    }

    /**
     * Créer et configurer le rôle super_admin avec toutes les permissions
     */
    private function createSuperAdminRole(): void
    {
        $this->command->info('👑 Création du rôle super_admin...');

        // Créer le rôle super_admin
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['guard_name' => 'web']
        );

        // Assigner TOUTES les permissions au super_admin
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $superAdminRole->syncPermissions($allPermissions);

        $this->command->info("   ✅ Rôle super_admin créé avec {$allPermissions->count()} permissions");
    }

    /**
     * Assigner les permissions panel aux rôles
     */
    private function assignPanelPermissions(): void
    {
        $this->command->info('🎭 Attribution des permissions panel...');

        $rolePanelMap = [
            'admin' => 'panel.admin',
            'manager' => 'panel.manager',
            'tech' => 'panel.tech',
            'source' => 'panel.source',
            'cinema' => 'panel.cinema',
            'supervisor' => 'panel.infrastructure',
        ];

        foreach ($rolePanelMap as $roleName => $panelPermission) {
            $role = Role::where('name', $roleName)->first();
            $permission = Permission::where('name', $panelPermission)->first();

            if ($role && $permission) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                    $this->command->info("   ✅ {$roleName} → {$panelPermission}");
                } else {
                    $this->command->info("   ⏭️  {$roleName} → {$panelPermission} (déjà assigné)");
                }
            } else {
                $this->command->warn("   ⚠️  Rôle '{$roleName}' ou permission '{$panelPermission}' introuvable");
            }
        }
    }

    /**
     * Assigner les rôles aux utilisateurs par défaut
     */
    private function assignRolesToUsers(): void
    {
        $this->command->info('👥 Attribution des rôles aux utilisateurs...');

        $userRoleMap = [
            'admin@dcprism.local' => ['admin', 'super_admin'], // Admin a les deux rôles
            'manager@dcprism.local' => ['manager'],
            'tech@dcprism.local' => ['tech'],
            'source@dcprism.local' => ['source'],
            'cinema@dcprism.local' => ['cinema'],
            'supervisor@dcprism.local' => ['supervisor'],
        ];

        foreach ($userRoleMap as $email => $roleNames) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Nettoyer les rôles existants
                $user->syncRoles([]);

                // Assigner les nouveaux rôles avec le bon guard
                foreach ($roleNames as $roleName) {
                    $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
                    if ($role) {
                        $user->assignRole($roleName); // Utiliser le nom du rôle plutôt que l'objet
                    }
                }

                $rolesString = implode(', ', $roleNames);
                $this->command->info("   ✅ {$user->name} ({$email}) → {$rolesString}");
            } else {
                $this->command->warn("   ⚠️  Utilisateur '{$email}' introuvable");
            }
        }
    }

    /**
     * Afficher un résumé des rôles et utilisateurs
     */
    private function showRolesSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Résumé de la configuration:');
        $this->command->info('');

        // Résumé des rôles
        $roles = Role::withCount('permissions', 'users')->get();
        $this->command->info('🎭 Rôles disponibles:');
        foreach ($roles as $role) {
            $this->command->info("   • {$role->name}: {$role->permissions_count} permissions, {$role->users_count} utilisateurs");
        }

        // Utilisateurs avec leurs rôles
        $this->command->info('');
        $this->command->info('👥 Utilisateurs et leurs rôles:');
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            $roleNames = $user->roles->pluck('name')->join(', ');
            $this->command->info("   • {$user->name} ({$user->email}): {$roleNames}");
        }

        // Permissions panel
        $this->command->info('');
        $this->command->info('🚪 Permissions d\'accès aux panels:');
        $panelPermissions = Permission::where('name', 'like', 'panel.%')->get();
        foreach ($panelPermissions as $permission) {
            $roles = Role::whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission->name);
            })->pluck('name')->join(', ');
            
            $this->command->info("   • {$permission->name}: {$roles}");
        }
    }
}
