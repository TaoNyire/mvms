<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\OrganizationProfile;

class SimpleOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the organization role
        $organizationRole = Role::where('name', 'organization')->first();

        // Organization 1: Malawi Red Cross Society
        $org1User = User::firstOrCreate(
            ['email' => 'info@malawiredcross.org'],
            [
                'name' => 'Malawi Red Cross Society',
                'password' => Hash::make('RedCross2024!'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$org1User->hasRole('organization')) {
            $org1User->roles()->attach($organizationRole);
        }

        if (!$org1User->organizationProfile) {
            $org1User->organizationProfile()->create([
                'org_name' => 'Malawi Red Cross Society',
                'description' => 'The Malawi Red Cross Society is a humanitarian organization that provides emergency assistance, disaster relief, and education in Malawi.',
                'email' => 'info@malawiredcross.org',
                'phone' => '+265 1 789 456',
                'website' => 'https://malawiredcross.org',
                'physical_address' => 'Red Cross House, City Centre',
                'district' => 'Lilongwe',
                'region' => 'Central',
                'registration_number' => 'NGO/R/001/2024',
                'is_registered' => true,
                'focus_areas' => ['Health', 'Disaster Relief', 'Community Development', 'Education'],
                'is_complete' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        }

        // Organization 2: World Vision Malawi
        $org2User = User::firstOrCreate(
            ['email' => 'info@worldvision.mw'],
            [
                'name' => 'World Vision Malawi',
                'password' => Hash::make('WorldVision2024!'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$org2User->hasRole('organization')) {
            $org2User->roles()->attach($organizationRole);
        }

        if (!$org2User->organizationProfile) {
            $org2User->organizationProfile()->create([
                'org_name' => 'World Vision Malawi',
                'description' => 'World Vision Malawi is a Christian humanitarian organization dedicated to working with children, families, and their communities.',
                'email' => 'info@worldvision.mw',
                'phone' => '+265 1 756 789',
                'website' => 'https://worldvision.org.mw',
                'physical_address' => 'World Vision House, Area 47',
                'district' => 'Lilongwe',
                'region' => 'Central',
                'registration_number' => 'NGO/R/002/2024',
                'is_registered' => true,
                'focus_areas' => ['Child Protection', 'Education', 'Health', 'Water & Sanitation'],
                'is_complete' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        }

        // Organization 3: Habitat for Humanity Malawi
        $org3User = User::firstOrCreate(
            ['email' => 'info@habitat.mw'],
            [
                'name' => 'Habitat for Humanity Malawi',
                'password' => Hash::make('Habitat2024!'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$org3User->hasRole('organization')) {
            $org3User->roles()->attach($organizationRole);
        }

        if (!$org3User->organizationProfile) {
            $org3User->organizationProfile()->create([
                'org_name' => 'Habitat for Humanity Malawi',
                'description' => 'Habitat for Humanity Malawi works toward a world where everyone has a decent place to live.',
                'email' => 'info@habitat.mw',
                'phone' => '+265 1 234 567',
                'website' => 'https://habitat.org.mw',
                'physical_address' => 'Habitat House, Old Town',
                'district' => 'Blantyre',
                'region' => 'Southern',
                'registration_number' => 'NGO/R/003/2024',
                'is_registered' => true,
                'focus_areas' => ['Housing', 'Community Development', 'Disaster Response'],
                'is_complete' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        }

        $this->command->info('✅ Successfully created 3 organizations!');
        $this->command->info('');
        $this->command->info('🏢 ORGANIZATION LOGIN CREDENTIALS:');
        $this->command->info('');
        $this->command->info('1. MALAWI RED CROSS SOCIETY:');
        $this->command->info('   📧 info@malawiredcross.org | 🔑 RedCross2024!');
        $this->command->info('');
        $this->command->info('2. WORLD VISION MALAWI:');
        $this->command->info('   📧 info@worldvision.mw | 🔑 WorldVision2024!');
        $this->command->info('');
        $this->command->info('3. HABITAT FOR HUMANITY MALAWI:');
        $this->command->info('   📧 info@habitat.mw | 🔑 Habitat2024!');
        $this->command->info('');
        $this->command->info('🌟 All accounts are email verified and ready to use!');
    }
}
