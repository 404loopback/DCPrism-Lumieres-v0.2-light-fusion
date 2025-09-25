<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Fresnel\app\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Seeder principal pour créer les utilisateurs avec les rôles Shield
 *
 * Ce seeder crée les utilisateurs et leur assigne les rôles Shield.
 * Les rôles doivent être créés au préalable par ShieldSeeder.
 *
 * NETTOYÉ : Plus de référence à la colonne 'role' legacy - 100% Shield
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@dcprism.local',
                'password' => Hash::make('admin123'), // Mot de passe spécifique pour admin
                'email_verified_at' => now(),
                'shield_role' => 'admin',
            ],
            [
                'name' => 'Tech User',
                'email' => 'tech@dcprism.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'shield_role' => 'tech',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@dcprism.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'shield_role' => 'manager',
            ],
            [
                'name' => 'Supervisor User',
                'email' => 'supervisor@dcprism.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'shield_role' => 'supervisor',
            ],
            [
                'name' => 'Source User',
                'email' => 'source@dcprism.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'shield_role' => 'source',
            ],
            [
                'name' => 'Cinema User',
                'email' => 'cinema@dcprism.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'shield_role' => 'cinema',
            ],
        ];

        foreach ($users as $userData) {
            // Séparer le rôle Shield des données utilisateur
            $shieldRole = $userData['shield_role'];
            unset($userData['shield_role']);

            // Créer ou mettre à jour l'utilisateur
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assigner le rôle Shield si il existe
            if ($shieldRole) {
                $user->syncRoles([$shieldRole]);
                $this->command->info("Assigned role '{$shieldRole}' to {$user->email}");
            }
        }

        $this->command->info('Users with Shield roles seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Comptes créés:');
        $this->command->info('  👑 Admin: admin@dcprism.local / admin123');
        $this->command->info('  🔧 Tech: tech@dcprism.local / password');
        $this->command->info('  👔 Manager: manager@dcprism.local / password');
        $this->command->info('  👥 Supervisor: supervisor@dcprism.local / password');
        $this->command->info('  🎦 Source: source@dcprism.local / password');
        $this->command->info('  🎭 Cinema: cinema@dcprism.local / password');
        $this->command->info('');

        // Statistiques des rôles
        $this->command->info('📈 Rôles assignés:');
        $roleStats = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->selectRaw('roles.name, COUNT(*) as count')
            ->get();

        foreach ($roleStats as $stat) {
            $this->command->info("  🎭 {$stat->name}: {$stat->count} utilisateur(s)");
        }
    }
}
