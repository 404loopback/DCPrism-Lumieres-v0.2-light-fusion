<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GenerateMultiPanelPermissions extends Command
{
    protected $signature = 'shield:multi-panel {--reset : Reset all panel permissions}';
    protected $description = 'Generate permissions for multi-panel access management';

    public function handle()
    {
        if ($this->option('reset')) {
            $this->resetPanelPermissions();
        }

        $this->generatePanelPermissions();
        $this->assignPanelPermissionsToRoles();
        
        $this->info('Multi-panel permissions generated successfully!');
    }

    private function resetPanelPermissions()
    {
        $panelPermissions = Permission::where('name', 'like', 'panel.%')->get();
        foreach ($panelPermissions as $permission) {
            $permission->delete();
        }
        $this->info('Panel permissions reset.');
    }

    private function generatePanelPermissions()
    {
        $panels = [
            'admin' => 'Access Admin Panel',
            'manager' => 'Access Manager Panel', 
            'tech' => 'Access Tech Panel',
            'source' => 'Access Source Panel',
            'cinema' => 'Access Cinema Panel',
            'infrastructure' => 'Access Infrastructure Panel'
        ];

        foreach ($panels as $panel => $description) {
            $permission = Permission::firstOrCreate([
                'name' => "panel.{$panel}",
                'guard_name' => 'web'
            ]);
            $this->line("Created permission: panel.{$panel}");
        }
    }

    private function assignPanelPermissionsToRoles()
    {
        $rolePermissions = [
            'super_admin' => ['panel.admin', 'panel.manager', 'panel.tech', 'panel.source', 'panel.cinema', 'panel.infrastructure'],
            'admin' => ['panel.admin', 'panel.manager', 'panel.tech'],
            'manager' => ['panel.manager'],
            'tech' => ['panel.tech'],
            'supervisor' => ['panel.admin', 'panel.manager'],
            'source' => ['panel.source'],
            'cinema' => ['panel.cinema']
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                    $this->line("Assigned {$permissionName} to {$roleName}");
                }
            }
        }
    }
}
