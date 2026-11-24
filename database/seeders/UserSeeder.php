<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Creates one starter Admin and one starter Officer account.
     * firstOrCreate means re-running the seeder never overwrites a changed password.
     *
     * CHANGE THESE PASSWORDS before the system holds any real data.
     */
    public function run(): void
    {
        $password = env('SEED_USER_PASSWORD', 'ChangeMe123!');

        User::firstOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'System Administrator',
                'password' => $password, // hashed automatically by the model cast
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'officer@example.test'],
            [
                'name' => 'Sample Officer',
                'password' => $password,
                'role' => User::ROLE_OFFICER,
                'is_active' => true,
            ]
        );
    }
}
