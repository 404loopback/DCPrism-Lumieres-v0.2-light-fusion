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
        // Créer un utilisateur administrateur de test
        // Utilisation du modèle User au lieu de DB::table pour éviter le double hachage
        User::create([
            'name' => 'Admin DCParty',
            'email' => 'admin@dcparty.local',
            'password' => 'dcparty123', // Le mutateur du modèle User va le hacher automatiquement
            'email_verified_at' => Carbon::now(),
            'is_active' => true,
        ]);

        // Créer un utilisateur de test standard
        User::create([
            'name' => 'Test User',
            'email' => 'test@dcparty.local',
            'password' => 'password', // Le mutateur du modèle User va le hacher automatiquement
            'email_verified_at' => Carbon::now(),
            'is_active' => true,
        ]);

        echo "✅ Utilisateurs créés :\n";
        echo "📧 admin@dcparty.local / dcparty123\n";
        echo "📧 test@dcparty.local / password\n";
    }
}
