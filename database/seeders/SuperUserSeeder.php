<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the admin role exists
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create the admin user (customize these details as needed)
        $admin = User::firstOrCreate([
            'email' => 'mvmsadmin@gmail.com',
        ], [
            'name' => 'Mvms Admin',
            'password' => Hash::make('mvmsadmin!'), // Change this password!
        ]);

        // Assign admin role to the user
        if (method_exists($admin, 'roles')) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
    }
}