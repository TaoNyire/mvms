<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\Skill;
use Carbon\Carbon;

class OpportunitySeeder extends Seeder
{
    public function run()
    {
        // Get an organization user (or create one)
        $orgUser = User::whereHas('roles', function($q) {
            $q->where('name', 'organization');
        })->first();

        if (!$orgUser) {
            // Create a test organization user
            $orgUser = User::create([
                'name' => 'Test Organization',
                'email' => 'org@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $orgUser->assignRole('organization');
        }

        // Get some skills
        $skills = Skill::take(5)->get();

        $opportunities = [
            [
                'title' => 'Community Garden Project',
                'description' => 'Help establish and maintain a community garden to provide fresh produce for local families in need.',
                'location' => 'Downtown Community Center, Lilongwe',
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(90),
                'volunteers_needed' => 8,
                'status' => 'active',
                'organization_id' => $orgUser->id,
            ],
            [
                'title' => 'Youth Education Support',
                'description' => 'Assist with after-school tutoring and mentoring programs for underprivileged children.',
                'location' => 'Lilongwe Primary School',
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addDays(180),
                'volunteers_needed' => 12,
                'status' => 'active',
                'organization_id' => $orgUser->id,
            ],
            [
                'title' => 'Healthcare Outreach Program',
                'description' => 'Support mobile health clinics in rural areas with patient registration and health education.',
                'location' => 'Rural Blantyre District',
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(120),
                'volunteers_needed' => 6,
                'status' => 'active',
                'organization_id' => $orgUser->id,
            ],
            [
                'title' => 'Environmental Conservation',
                'description' => 'Join tree planting and environmental conservation efforts in local communities.',
                'location' => 'Zomba District',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(150),
                'volunteers_needed' => 15,
                'status' => 'active',
                'organization_id' => $orgUser->id,
            ],
            [
                'title' => 'Digital Literacy Training',
                'description' => 'Teach basic computer skills and digital literacy to adults in rural communities.',
                'location' => 'Mzuzu Community Center',
                'start_date' => Carbon::now()->addDays(21),
                'end_date' => Carbon::now()->addDays(200),
                'volunteers_needed' => 8,
                'status' => 'active',
                'organization_id' => $orgUser->id,
            ],
        ];

        foreach ($opportunities as $oppData) {
            $opportunity = Opportunity::create($oppData);
            
            // Attach random skills to each opportunity
            if ($skills->count() > 0) {
                $randomSkills = $skills->random(rand(2, 4));
                $opportunity->skills()->attach($randomSkills->pluck('id'));
            }
        }

        $this->command->info('Created ' . count($opportunities) . ' sample opportunities');
    }
}
