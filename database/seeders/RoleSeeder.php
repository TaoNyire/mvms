<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Define the roles
        $roles = [
            'admin',
            'volunteer',
            'organization',
        ];

        // Insert roles into the database
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
