<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ROLE_ADMIN,
                'guard_name' => 'web',
                'display_name' => 'Administrateur',
                'description' => 'Accès complet au système DCPrism',
                'permissions' => ['*'], // Toutes les permissions
            ],
            [
                'name' => Role::ROLE_SUPERVISOR,
                'guard_name' => 'web',
                'display_name' => 'Superviseur',
                'description' => 'Supervision des festivals et validation',
                'permissions' => [
                    'movies.view', 'movies.validate', 'movies.reject',
                    'festivals.view', 'festivals.manage',
                    'users.view', 'reports.view'
                ],
            ],
            [
                'name' => Role::ROLE_SOURCE,
                'guard_name' => 'web',
                'display_name' => 'Source/Producteur',
                'description' => 'Upload et gestion de films',
                'permissions' => [
                    'movies.create', 'movies.edit_own', 'movies.view_own',
                    'uploads.create'
                ],
            ],
            [
                'name' => Role::ROLE_CINEMA,
                'guard_name' => 'web',
                'display_name' => 'Cinéma',
                'description' => 'Accès aux films validés pour projection',
                'permissions' => [
                    'movies.view_validated', 'movies.download',
                    'kdm.request'
                ],
            ],
            [
                'name' => Role::ROLE_VALIDATOR,
                'guard_name' => 'web',
                'display_name' => 'Validateur Technique',
                'description' => 'Validation technique des DCP',
                'permissions' => [
                    'movies.view', 'movies.validate', 'movies.reject',
                    'movies.test', 'technical.access'
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
