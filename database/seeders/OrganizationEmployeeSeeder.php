<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\OrganizationProfile;

class OrganizationEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the organization role
        $organizationRole = Role::where('name', 'organization')->first();

        // Red Cross Employees
        $redCrossEmployees = [
            [
                'name' => 'Grace Banda',
                'email' => 'grace.banda@malawiredcross.org',
                'password' => 'RedCross2024!',
                'position' => 'Program Director',
                'org_name' => 'Malawi Red Cross Society'
            ],
            [
                'name' => 'James Mwale',
                'email' => 'james.mwale@malawiredcross.org',
                'password' => 'RedCross2024!',
                'position' => 'Volunteer Coordinator',
                'org_name' => 'Malawi Red Cross Society'
            ],
            [
                'name' => 'Mary Phiri',
                'email' => 'mary.phiri@malawiredcross.org',
                'password' => 'RedCross2024!',
                'position' => 'Community Outreach Manager',
                'org_name' => 'Malawi Red Cross Society'
            ]
        ];

        // World Vision Employees
        $worldVisionEmployees = [
            [
                'name' => 'Peter Kachale',
                'email' => 'peter.kachale@worldvision.mw',
                'password' => 'WorldVision2024!',
                'position' => 'Country Director',
                'org_name' => 'World Vision Malawi'
            ],
            [
                'name' => 'Sarah Tembo',
                'email' => 'sarah.tembo@worldvision.mw',
                'password' => 'WorldVision2024!',
                'position' => 'Volunteer Program Manager',
                'org_name' => 'World Vision Malawi'
            ],
            [
                'name' => 'Daniel Chirwa',
                'email' => 'daniel.chirwa@worldvision.mw',
                'password' => 'WorldVision2024!',
                'position' => 'Field Operations Coordinator',
                'org_name' => 'World Vision Malawi'
            ]
        ];

        // Habitat Employees
        $habitatEmployees = [
            [
                'name' => 'Elizabeth Nyirenda',
                'email' => 'elizabeth.nyirenda@habitat.mw',
                'password' => 'Habitat2024!',
                'position' => 'Executive Director',
                'org_name' => 'Habitat for Humanity Malawi'
            ],
            [
                'name' => 'Michael Gondwe',
                'email' => 'michael.gondwe@habitat.mw',
                'password' => 'Habitat2024!',
                'position' => 'Volunteer Engagement Specialist',
                'org_name' => 'Habitat for Humanity Malawi'
            ],
            [
                'name' => 'Ruth Msiska',
                'email' => 'ruth.msiska@habitat.mw',
                'password' => 'Habitat2024!',
                'position' => 'Community Relations Officer',
                'org_name' => 'Habitat for Humanity Malawi'
            ]
        ];

        // Combine all employees
        $allEmployees = array_merge($redCrossEmployees, $worldVisionEmployees, $habitatEmployees);

        foreach ($allEmployees as $employeeData) {
            $user = User::firstOrCreate(
                ['email' => $employeeData['email']],
                [
                    'name' => $employeeData['name'],
                    'password' => Hash::make($employeeData['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->hasRole('organization')) {
                $user->roles()->attach($organizationRole);
            }

            if (!$user->organizationProfile) {
                $district = str_contains($employeeData['org_name'], 'Habitat') ? 'Blantyre' : 'Lilongwe';
                $region = str_contains($employeeData['org_name'], 'Habitat') ? 'Southern' : 'Central';

                $user->organizationProfile()->create([
                    'org_name' => $employeeData['org_name'],
                    'contact_person_name' => $employeeData['name'],
                    'contact_person_title' => $employeeData['position'],
                    'contact_person_email' => $employeeData['email'],
                    'contact_person_phone' => '+265 99' . rand(1000000, 9999999),
                    'phone' => '+265 99' . rand(1000000, 9999999),
                    'district' => $district,
                    'region' => $region,
                    'additional_info' => 'Employee of ' . $employeeData['org_name'],
                    'is_complete' => true,
                ]);
            }
        }

        $this->command->info('✅ Successfully created organization employees!');
        $this->command->info('');
        $this->command->info('👥 EMPLOYEE LOGIN CREDENTIALS:');
        $this->command->info('');
        
        $this->command->info('🔴 MALAWI RED CROSS SOCIETY EMPLOYEES:');
        foreach ($redCrossEmployees as $emp) {
            $this->command->info("   📧 {$emp['email']} | 🔑 {$emp['password']} ({$emp['position']})");
        }
        $this->command->info('');
        
        $this->command->info('🌍 WORLD VISION MALAWI EMPLOYEES:');
        foreach ($worldVisionEmployees as $emp) {
            $this->command->info("   📧 {$emp['email']} | 🔑 {$emp['password']} ({$emp['position']})");
        }
        $this->command->info('');
        
        $this->command->info('🏠 HABITAT FOR HUMANITY MALAWI EMPLOYEES:');
        foreach ($habitatEmployees as $emp) {
            $this->command->info("   📧 {$emp['email']} | 🔑 {$emp['password']} ({$emp['position']})");
        }
        $this->command->info('');
        $this->command->info('🌟 All employee accounts are email verified and ready to use!');
    }
}
