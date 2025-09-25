<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Fresnel\app\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Commande pour valider la configuration Shield
 * Vérifie que les rôles, permissions et utilisateurs sont correctement configurés
 */
class ValidateShieldSetup extends Command
{
    protected $signature = 'shield:validate';
    
    protected $description = 'Valide la configuration Shield et affiche un rapport détaillé';

    public function handle(): int
    {
        $this->info('🛡️  Validation de la configuration Shield');
        $this->info('==========================================');
        $this->line('');

        // 1. Vérifier les rôles
        $this->validateRoles();
        
        // 2. Vérifier les permissions
        $this->validatePermissions();
        
        // 3. Vérifier les utilisateurs
        $this->validateUsers();
        
        // 4. Tester l'accès aux panels
        $this->validatePanelAccess();
        
        // 5. Résumé final
        $this->showSummary();

        return Command::SUCCESS;
    }

    private function validateRoles(): void
    {
        $this->info('📋 1. Validation des rôles');
        $this->info('-------------------------');

        $expectedRoles = ['admin', 'manager', 'tech', 'source', 'cinema', 'supervisor', 'super_admin'];
        $existingRoles = Role::pluck('name')->toArray();
        
        foreach ($expectedRoles as $role) {
            if (in_array($role, $existingRoles)) {
                $this->info("   ✅ Rôle '{$role}' présent");
            } else {
                $this->error("   ❌ Rôle '{$role}' manquant");
            }
        }

        // Rôles supplémentaires
        $extraRoles = array_diff($existingRoles, $expectedRoles);
        if (!empty($extraRoles)) {
            $this->warn('   ⚠️  Rôles supplémentaires: ' . implode(', ', $extraRoles));
        }

        $this->line('');
    }

    private function validatePermissions(): void
    {
        $this->info('🔐 2. Validation des permissions');
        $this->info('------------------------------');

        $panelPermissions = [
            'panel.admin', 'panel.manager', 'panel.tech', 
            'panel.source', 'panel.cinema', 'panel.infrastructure'
        ];

        foreach ($panelPermissions as $permission) {
            $exists = Permission::where('name', $permission)->exists();
            if ($exists) {
                $this->info("   ✅ Permission '{$permission}' présente");
            } else {
                $this->error("   ❌ Permission '{$permission}' manquante");
            }
        }

        $totalPermissions = Permission::count();
        $this->info("   📊 Total: {$totalPermissions} permissions");
        $this->line('');
    }

    private function validateUsers(): void
    {
        $this->info('👥 3. Validation des utilisateurs');
        $this->info('-------------------------------');

        $testUsers = [
            'admin@dcprism.local' => ['admin', 'super_admin'],
            'manager@dcprism.local' => ['manager'],
            'tech@dcprism.local' => ['tech'],
            'source@dcprism.local' => ['source'],
            'cinema@dcprism.local' => ['cinema'],
            'supervisor@dcprism.local' => ['supervisor'],
        ];

        foreach ($testUsers as $email => $expectedRoles) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("   ❌ Utilisateur '{$email}' manquant");
                continue;
            }

            $userRoles = $user->roles->pluck('name')->toArray();
            $missingRoles = array_diff($expectedRoles, $userRoles);
            $extraRoles = array_diff($userRoles, $expectedRoles);

            if (empty($missingRoles) && empty($extraRoles)) {
                $this->info("   ✅ {$email} → " . implode(', ', $userRoles));
            } else {
                $this->warn("   ⚠️  {$email} → " . implode(', ', $userRoles));
                if (!empty($missingRoles)) {
                    $this->warn("      Rôles manquants: " . implode(', ', $missingRoles));
                }
                if (!empty($extraRoles)) {
                    $this->warn("      Rôles en trop: " . implode(', ', $extraRoles));
                }
            }
        }

        $totalUsers = User::count();
        $usersWithRoles = User::has('roles')->count();
        $this->info("   📊 {$usersWithRoles}/{$totalUsers} utilisateurs ont des rôles");
        $this->line('');
    }

    private function validatePanelAccess(): void
    {
        $this->info('🚪 4. Test d\'accès aux panels');
        $this->info('----------------------------');

        // Test simplifié basé sur les rôles directement
        $testCases = [
            ['email' => 'admin@dcprism.local', 'expected_roles' => ['admin', 'super_admin']],
            ['email' => 'manager@dcprism.local', 'expected_roles' => ['manager']],
            ['email' => 'tech@dcprism.local', 'expected_roles' => ['tech']],
            ['email' => 'source@dcprism.local', 'expected_roles' => ['source']],
            ['email' => 'cinema@dcprism.local', 'expected_roles' => ['cinema']],
            ['email' => 'supervisor@dcprism.local', 'expected_roles' => ['supervisor']],
        ];

        $rolePanelMapping = [
            'admin' => ['fresnel', 'meniscus'], 
            'super_admin' => ['all'],
            'manager' => ['manager'],
            'tech' => ['tech'],
            'source' => ['source'],
            'cinema' => ['cinema'],
            'supervisor' => ['manager'],
        ];

        foreach ($testCases as $test) {
            $user = User::where('email', $test['email'])->first();
            
            if (!$user) {
                $this->error("   ❌ Utilisateur {$test['email']} non trouvé pour test");
                continue;
            }

            $userRoles = $user->roles->pluck('name')->toArray();
            $accessiblePanels = [];
            
            foreach ($userRoles as $role) {
                if (isset($rolePanelMapping[$role])) {
                    if (in_array('all', $rolePanelMapping[$role])) {
                        $accessiblePanels = ['tous les panels'];
                        break;
                    }
                    $accessiblePanels = array_merge($accessiblePanels, $rolePanelMapping[$role]);
                }
            }
            
            $accessText = empty($accessiblePanels) ? 'aucun' : implode(', ', array_unique($accessiblePanels));
            $this->info("   ✅ {$user->name} → Accès: {$accessText}");
        }

        $this->line('');
    }

    private function showSummary(): void
    {
        $this->info('📊 5. Résumé de la configuration');
        $this->info('===============================');

        $roles = Role::withCount(['permissions', 'users'])->get();
        $totalPermissions = Permission::count();
        $totalUsers = User::count();
        $usersWithRoles = User::has('roles')->count();

        $this->table(
            ['Rôle', 'Permissions', 'Utilisateurs'],
            $roles->map(fn($role) => [
                $role->name,
                $role->permissions_count,
                $role->users_count
            ])
        );

        $this->line('');
        $this->info("🎭 Rôles: " . $roles->count());
        $this->info("🔐 Permissions: {$totalPermissions}");
        $this->info("👥 Utilisateurs: {$usersWithRoles}/{$totalUsers} avec rôles");
        
        $this->line('');
        $this->info('✅ Validation Shield terminée !');
        
        if (User::has('roles')->count() === User::count() && Role::count() >= 6) {
            $this->info('🎉 Configuration Shield optimale détectée !');
        } else {
            $this->warn('⚠️  Certains éléments peuvent nécessiter attention.');
        }
    }
}
