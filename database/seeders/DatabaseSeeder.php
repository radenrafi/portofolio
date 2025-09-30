<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed a static Super Admin (do not use in production as-is)
        if (!User::where('email', 'superadmin@critispace.local')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@critispace.local',
                'password' => Hash::make('SuperAdmin!123'),
                'role' => \App\Models\User::ROLE_SUPER_ADMIN,
                'status' => \App\Models\User::STATUS_ACTIVE,
            ]);
        }

        // Example sample user for development
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'role' => \App\Models\User::ROLE_STUDENT,
                'status' => \App\Models\User::STATUS_ACTIVE,
            ]);
        }
    }
}
