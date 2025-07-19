<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first (required for user role assignments)
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            SimpleOrganizationSeeder::class,
            OrganizationEmployeeSeeder::class,
            OpportunitySeeder::class,
            VolunteerSeeder::class,
        ]);

        $this->command->info('Database seeding completed successfully!');
    }
}
