<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou mettre à jour un utilisateur admin par défaut
        User::updateOrCreate(
            ["email" => "admin@dcprism.local"],
            [
                "name" => "Admin DCPrism",
                "email_verified_at" => now(),
                "password" => Hash::make("admin123"),
            ]
        );

        $this->command->info("Utilisateur admin créé avec succès !");
        $this->command->info("Email: admin@dcprism.local");
        $this->command->info("Mot de passe: admin123");
    }
}
