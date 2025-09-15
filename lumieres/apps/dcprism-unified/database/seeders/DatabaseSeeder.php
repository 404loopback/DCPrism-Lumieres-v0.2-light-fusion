<?php

namespace Database\Seeders;

use App\Models\User;
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
            UserSeeder::class,                 // Utilisateurs avec rôles Shield
            
            // Seeders de référentiel
            LanguageSeeder::class,
            
            // Seeders métier
            FestivalSeeder::class,
            MovieSeeder::class,
            
            // Seeders nomenclature (dans l'ordre)
            ParameterSeeder::class,
            NomenclatureSeeder::class,
            NewDCPParametersSeeder::class,
            FresnelSeeder::class,
        ]);
    }
}
