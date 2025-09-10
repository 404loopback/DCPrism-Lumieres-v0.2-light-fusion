<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Tech User',
                'email' => 'tech@dcprism.local',
                'password' => Hash::make('password'),
                'role' => 'tech',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@dcprism.local',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Supervisor User',
                'email' => 'supervisor@dcprism.local',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Source User',
                'email' => 'source@dcprism.local',
                'password' => Hash::make('password'),
                'role' => 'source',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Cinema User',
                'email' => 'cinema@dcprism.local',
                'password' => Hash::make('password'),
                'role' => 'cinema',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Users seeded successfully!');
    }
}
