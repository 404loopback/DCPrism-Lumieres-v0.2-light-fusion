<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if admin user already exists
        $adminUser = User::where('email', 'admin@dcparty.local')->first();

        if (!$adminUser) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@dcparty.local',
                'password' => 'AdminPassword123!', // This will be hashed automatically
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            echo "Admin user created successfully!\n";
            echo "Email: admin@dcparty.local\n";
            echo "Password: AdminPassword123!\n";
        } else {
            echo "Admin user already exists.\n";
        }

        // Check if regular user exists
        $regularUser = User::where('email', 'user@dcparty.local')->first();

        if (!$regularUser) {
            User::create([
                'name' => 'Regular User',
                'email' => 'user@dcparty.local',
                'password' => 'UserPassword123!', // This will be hashed automatically
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            echo "Regular user created successfully!\n";
            echo "Email: user@dcparty.local\n";
            echo "Password: UserPassword123!\n";
        } else {
            echo "Regular user already exists.\n";
        }
    }
}
