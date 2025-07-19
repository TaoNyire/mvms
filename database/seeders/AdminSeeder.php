<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'tao admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'account_status' => 'active',
                'activated_at' => now(),
                'last_login_at' => now(),
            ]
        );

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole && !$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }

        // Create additional admin users for testing
        $additionalAdmins = [
            [
                'name' => 'John Admin',
                'email' => 'john.admin@mvms.mw',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Jane Admin',
                'email' => 'jane.admin@mvms.mw',
                'password' => Hash::make('password123'),
            ]
        ];

        foreach ($additionalAdmins as $adminData) {
            $user = User::firstOrCreate(
                ['email' => $adminData['email']],
                array_merge($adminData, ['email_verified_at' => now()])
            );

            if ($adminRole && !$user->roles()->where('role_id', $adminRole->id)->exists()) {
                $user->roles()->attach($adminRole);
            }
        }

        // Create sample organization user
        $orgUser = User::firstOrCreate(
            ['email' => 'org@mvms.mw'],
            [
                'name' => 'Sample Organization',
                'email' => 'org@mvms.mw',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $orgRole = Role::where('name', 'organization')->first();
        if ($orgRole && !$orgUser->roles()->where('role_id', $orgRole->id)->exists()) {
            $orgUser->roles()->attach($orgRole);
        }

        // Create an incomplete organization profile to test the profile completion flow
        if (!$orgUser->organizationProfile) {
            \App\Models\OrganizationProfile::create([
                'user_id' => $orgUser->id,
                'org_name' => $orgUser->name,
                'email' => $orgUser->email,
                'is_complete' => false,
                'active' => true,
                'status' => 'pending'
            ]);
        }

        // Create sample volunteer user
        $volunteerUser = User::firstOrCreate(
            ['email' => 'volunteer@mvms.mw'],
            [
                'name' => 'Sample Volunteer',
                'email' => 'volunteer@mvms.mw',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $volunteerRole = Role::where('name', 'volunteer')->first();
        if ($volunteerRole && !$volunteerUser->roles()->where('role_id', $volunteerRole->id)->exists()) {
            $volunteerUser->roles()->attach($volunteerRole);
        }

        // Create an incomplete volunteer profile to test the profile completion flow
        if (!$volunteerUser->volunteerProfile) {
            \App\Models\VolunteerProfile::create([
                'user_id' => $volunteerUser->id,
                'is_complete' => false,
                'is_active' => true,
            ]);
        }

        $this->command->info('Admin users created successfully!');
        $this->command->info('Admin: admin@mvms.mw / admin123');
        $this->command->info('Organization: org@mvms.mw / password123');
        $this->command->info('Volunteer: volunteer@mvms.mw / password123');
    }
}
