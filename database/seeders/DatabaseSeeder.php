<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Only accounts are seeded. Violation categories are NOT seeded on purpose:
     * the official list will come from the Student Council and is entered by the
     * Admin through the Violation Categories screen (built in a later step).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
