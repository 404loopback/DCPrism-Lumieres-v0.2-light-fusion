<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'utilisateur administrateur principal de DCPrism
        User::create([
            'name' => 'meniscus_admin',
            'email' => 'admin@dcprism.be',
            'password' => 'password', // Le mutateur du modèle User va le hacher automatiquement
            'role' => 'admin',
            'email_verified_at' => Carbon::now(),
            'is_active' => true,
        ]);

        // Créer un utilisateur de test standard
        User::create([
            'name' => 'Test User',
            'email' => 'test@dcprism.be',
            'password' => 'password', // Le mutateur du modèle User va le hacher automatiquement
            'role' => 'user',
            'email_verified_at' => Carbon::now(),
            'is_active' => true,
        ]);

        echo "✅ Utilisateurs DCPrism créés :\n";
        echo "👤 Admin: admin@dcprism.be / password (role: admin)\n";
        echo "👤 Test: test@dcprism.be / password (role: user)\n";
    }
}
