<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PanelPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $panelPermissions = [
            'panel.admin',
            'panel.manager', 
            'panel.tech',
            'panel.source',
            'panel.cinema',
            'panel.infrastructure',
        ];

        // Créer les permissions panel si elles n'existent pas
        foreach ($panelPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Assigner les permissions aux rôles correspondants
        $rolePermissionMapping = [
            'admin' => ['panel.admin'],
            'manager' => ['panel.manager'],
            'tech' => ['panel.tech'],
            'source' => ['panel.source'],
            'cinema' => ['panel.cinema'],
            'supervisor' => ['panel.manager'], // Superviseur utilise le panel manager
            'super_admin' => $panelPermissions, // Super admin a accès à tous les panels
        ];

        foreach ($rolePermissionMapping as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
                $this->command->info("Panel permissions assigned to role: {$roleName}");
            }
        }
    }
}
