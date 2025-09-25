<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeders de base - Shield et utilisateurs
            ShieldSeeder::class,               // Rôles et permissions Shield
            PanelPermissionSeeder::class,      // Permissions d'accès aux panels
            UserSeeder::class,                 // Utilisateurs avec rôles Shield
            ShieldRoleAssignmentSeeder::class, // Configuration complète des rôles (super_admin, panels, assignations)

            // Seeders de référentiel
            LanguageSeeder::class,

            // Seeders métier
            FestivalSeeder::class,
            MovieSeeder::class,

            // Seeders nomenclature (dans l'ordre)
            ParameterSeeder::class,
            FestivalParameterSeeder::class, // Associations festival-paramètres avec logique système
            NomenclatureSeeder::class,
            NewDCPParametersSeeder::class,
            FresnelSeeder::class,
        ]);
    }
}
