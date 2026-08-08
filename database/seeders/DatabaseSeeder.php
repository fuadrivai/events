<?php

namespace Database\Seeders;

use App\Models\GoogleOwnerToken;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@mutiaraharapan.sch.id',
            'password' => '$2y$12$51XP/OPWRJkD1HoBkoMMUOL6zb/EaxZ.B53hQPFJOuVhSM.995U0K', // password
            'role' => 'admin',
            'is_admin' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Primary',
            'email' => 'primary@mutiaraharapan.sch.id',
            'password' => '$2y$12$/VwI1JosbAbe/Q41aV8JtOkrF.oE7ubkUqlWBMg8Nnc3IMewU/CcO', // mutiaraharapan
            'role' => 'user',
            'is_admin' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Secondary',
            'email' => 'secondary@mutiaraharapan.sch.id',
            'password' => '$2y$12$/VwI1JosbAbe/Q41aV8JtOkrF.oE7ubkUqlWBMg8Nnc3IMewU/CcO', // mutiaraharapan
            'role' => 'user',
            'is_admin' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Preschool',
            'email' => 'preschool@mutiaraharapan.sch.id',
            'password' => '$2y$12$/VwI1JosbAbe/Q41aV8JtOkrF.oE7ubkUqlWBMg8Nnc3IMewU/CcO', // mutiaraharapan
            'role' => 'user',
            'is_admin' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Development Class',
            'email' => 'dc@mutiaraharapan.sch.id',
            'password' => '$2y$12$/VwI1JosbAbe/Q41aV8JtOkrF.oE7ubkUqlWBMg8Nnc3IMewU/CcO', // mutiaraharapan
            'role' => 'user',
            'is_admin' => 0,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
