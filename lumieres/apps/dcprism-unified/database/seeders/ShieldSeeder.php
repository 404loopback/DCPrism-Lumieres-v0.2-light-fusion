<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder pour créer les rôles et assigner les permissions Shield
 * Compatible avec Filament Shield v4+
 */
class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Réinitialiser le cache des permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Configuration des rôles avec leurs permissions Shield
        $rolePermissions = [
            'admin' => [
                // Admin a accès à tout - permissions basées sur les entités générées par Shield
                'View:User', 'ViewAny:User', 'Create:User', 'Update:User', 'Delete:User',
                'View:Movie', 'ViewAny:Movie', 'Create:Movie', 'Update:Movie', 'Delete:Movie',
                'View:Festival', 'ViewAny:Festival', 'Create:Festival', 'Update:Festival', 'Delete:Festival',
                'View:Dcp', 'ViewAny:Dcp', 'Create:Dcp', 'Update:Dcp', 'Delete:Dcp',
                'View:Parameter', 'ViewAny:Parameter', 'Create:Parameter', 'Update:Parameter', 'Delete:Parameter',
                'View:Nomenclature', 'ViewAny:Nomenclature', 'Create:Nomenclature', 'Update:Nomenclature', 'Delete:Nomenclature',
                'View:Lang', 'ViewAny:Lang', 'Create:Lang', 'Update:Lang', 'Delete:Lang',
                'View:Version', 'ViewAny:Version', 'Create:Version', 'Update:Version', 'Delete:Version',
                'View:ValidationResult', 'ViewAny:ValidationResult', 'Create:ValidationResult', 'Update:ValidationResult', 'Delete:ValidationResult',
                'View:MovieMetadata', 'ViewAny:MovieMetadata', 'Create:MovieMetadata', 'Update:MovieMetadata', 'Delete:MovieMetadata',
            ],
            'manager' => [
                'View:User', 'ViewAny:User',
                'View:Movie', 'ViewAny:Movie', 'Create:Movie', 'Update:Movie', 'Delete:Movie',
                'View:Festival', 'ViewAny:Festival', 'Update:Festival',
                'View:Dcp', 'ViewAny:Dcp', 'Create:Dcp', 'Update:Dcp',
                'View:Parameter', 'ViewAny:Parameter', 'Update:Parameter',
                'View:Nomenclature', 'ViewAny:Nomenclature', 'Create:Nomenclature', 'Update:Nomenclature',
                'View:Lang', 'ViewAny:Lang',
                'View:Version', 'ViewAny:Version', 'Create:Version', 'Update:Version',
            ],
            'tech' => [
                'View:Movie', 'ViewAny:Movie',
                'View:Dcp', 'ViewAny:Dcp', 'Update:Dcp',
                'View:Nomenclature', 'ViewAny:Nomenclature',
                'View:ValidationResult', 'ViewAny:ValidationResult', 'Create:ValidationResult', 'Update:ValidationResult',
                'View:Version', 'ViewAny:Version',
            ],
            'source' => [
                'View:Movie', 'ViewAny:Movie', 'Create:Movie', 'Update:Movie',
                'View:Dcp', 'ViewAny:Dcp', 'Create:Dcp',
                'View:Version', 'ViewAny:Version', 'Create:Version',
                'View:MovieMetadata', 'ViewAny:MovieMetadata', 'Create:MovieMetadata', 'Update:MovieMetadata',
            ],
            'cinema' => [
                'View:Movie', 'ViewAny:Movie',
                'View:Dcp', 'ViewAny:Dcp',
                'View:Version', 'ViewAny:Version',
                'View:Festival', 'ViewAny:Festival',
            ],
            'supervisor' => [
                'View:User', 'ViewAny:User',
                'View:Movie', 'ViewAny:Movie', 'Create:Movie', 'Update:Movie',
                'View:Festival', 'ViewAny:Festival', 'Update:Festival',
                'View:Dcp', 'ViewAny:Dcp', 'Create:Dcp', 'Update:Dcp',
                'View:Parameter', 'ViewAny:Parameter',
                'View:Nomenclature', 'ViewAny:Nomenclature', 'Update:Nomenclature',
                'View:Version', 'ViewAny:Version', 'Create:Version', 'Update:Version',
                'View:ValidationResult', 'ViewAny:ValidationResult',
            ],
        ];

        $this->command->info('🛡️  Création des rôles et attribution des permissions Shield...');

        foreach ($rolePermissions as $roleName => $permissionNames) {
            // Créer le rôle s'il n'existe pas
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['guard_name' => 'web']
            );

            // Vider les permissions existantes
            $role->permissions()->detach();

            // Assigner les nouvelles permissions une par une
            $assignedCount = 0;
            foreach ($permissionNames as $permissionName) {
                $permission = Permission::where('name', $permissionName)
                    ->where('guard_name', 'web')
                    ->first();
                    
                if ($permission) {
                    $role->givePermissionTo($permission);
                    $assignedCount++;
                } else {
                    $this->command->warn("⚠️  Permission '{$permissionName}' non trouvée pour le rôle '{$roleName}'");
                }
            }

            $this->command->info("🎭 Rôle '{$roleName}' configuré avec {$assignedCount}/" . count($permissionNames) . " permissions");
        }

        $this->command->info('');
        $this->command->info('✅ Rôles et permissions Shield créés avec succès!');
        $this->command->info('📝 Rôles disponibles: ' . Role::pluck('name')->join(', '));
        $this->command->info('🔑 ' . Permission::count() . ' permissions au total');
    }
}
