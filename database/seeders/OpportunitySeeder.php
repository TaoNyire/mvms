<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\Role;

class OpportunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get organization users
        $orgRole = Role::where('name', 'organization')->first();
        $organizations = User::whereHas('roles', function($query) use ($orgRole) {
            $query->where('role_id', $orgRole->id);
        })->get();

        if ($organizations->isEmpty()) {
            $this->command->warn('No organization users found. Please run AdminSeeder first.');
            return;
        }

        $opportunities = [
            [
                'title' => 'Community Health Education Volunteer',
                'description' => 'Join our team to educate communities about health and hygiene practices. You will visit villages and conduct health awareness sessions, distribute educational materials, and help improve community health outcomes.',
                'category' => 'Health',
                'type' => 'recurring',
                'urgency' => 'high',
                'location_type' => 'physical',
                'address' => 'Various villages in Lilongwe Rural',
                'district' => 'Lilongwe',
                'region' => 'Central',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addMonths(3),
                'start_time' => '08:00',
                'end_time' => '16:00',
                'duration_hours' => 8,
                'volunteers_needed' => 5,
                'required_skills' => ['Teaching', 'Healthcare', 'Social Work'],
                'requirements' => 'Basic knowledge of health and hygiene practices. Ability to communicate in local languages preferred.',
                'benefits' => 'Transportation provided, meals during field visits, certificate of participation.',
                'provides_transport' => true,
                'provides_meals' => true,
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Youth Technology Training Facilitator',
                'description' => 'Help young people develop digital skills by facilitating computer literacy and basic programming workshops. This opportunity involves teaching basic computer skills, internet safety, and introduction to coding.',
                'category' => 'Technology',
                'type' => 'one_time',
                'urgency' => 'medium',
                'location_type' => 'physical',
                'address' => 'Blantyre Youth Center, Blantyre',
                'district' => 'Blantyre',
                'region' => 'Southern',
                'start_date' => now()->addDays(14),
                'start_time' => '09:00',
                'end_time' => '15:00',
                'duration_hours' => 6,
                'volunteers_needed' => 3,
                'required_skills' => ['Technology', 'Teaching'],
                'requirements' => 'Experience with computers and basic programming. Teaching or training experience preferred.',
                'benefits' => 'Lunch provided, networking opportunities with tech professionals.',
                'provides_meals' => true,
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Environmental Conservation Project',
                'description' => 'Participate in tree planting and environmental conservation activities. Help restore degraded lands, plant indigenous trees, and educate communities about environmental protection.',
                'category' => 'Environment',
                'type' => 'one_time',
                'urgency' => 'urgent',
                'location_type' => 'physical',
                'address' => 'Zomba Plateau Forest Reserve',
                'district' => 'Zomba',
                'region' => 'Southern',
                'start_date' => now()->addDays(5),
                'start_time' => '06:00',
                'end_time' => '14:00',
                'duration_hours' => 8,
                'volunteers_needed' => 20,
                'required_skills' => ['Agriculture', 'Environment'],
                'requirements' => 'Physical fitness required. Outdoor work experience preferred.',
                'benefits' => 'Transportation from Zomba town, breakfast and lunch provided, certificate.',
                'provides_transport' => true,
                'provides_meals' => true,
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Adult Literacy Program Instructor',
                'description' => 'Teach reading and writing skills to adults who missed formal education opportunities. Help improve literacy rates in rural communities through evening classes.',
                'category' => 'Education',
                'type' => 'recurring',
                'urgency' => 'medium',
                'location_type' => 'physical',
                'address' => 'Kasungu Community Center',
                'district' => 'Kasungu',
                'region' => 'Central',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addMonths(6),
                'start_time' => '18:00',
                'end_time' => '20:00',
                'duration_hours' => 2,
                'volunteers_needed' => 4,
                'required_skills' => ['Teaching', 'Administration'],
                'requirements' => 'Teaching experience or education background. Patience and cultural sensitivity required.',
                'benefits' => 'Small stipend provided, training materials, recognition certificate.',
                'is_paid' => true,
                'payment_amount' => 2000,
                'payment_frequency' => 'daily',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Emergency Food Distribution Support',
                'description' => 'Assist in organizing and distributing emergency food supplies to families affected by recent floods. Help with registration, packaging, and distribution of relief items.',
                'category' => 'Emergency',
                'type' => 'one_time',
                'urgency' => 'urgent',
                'location_type' => 'physical',
                'address' => 'Nsanje District Council Offices',
                'district' => 'Nsanje',
                'region' => 'Southern',
                'start_date' => now()->addDays(2),
                'start_time' => '07:00',
                'end_time' => '17:00',
                'duration_hours' => 10,
                'volunteers_needed' => 15,
                'required_skills' => ['Administration', 'Social Work'],
                'requirements' => 'Physical fitness for lifting and moving supplies. Experience in emergency response preferred.',
                'benefits' => 'Meals provided, transportation reimbursement, volunteer certificate.',
                'provides_meals' => true,
                'status' => 'published',
                'published_at' => now(),
            ]
        ];

        foreach ($opportunities as $index => $opportunityData) {
            // Assign to different organizations
            $org = $organizations[$index % $organizations->count()];
            $opportunityData['organization_id'] = $org->id;
            $opportunityData['contact_email'] = $org->email;
            $opportunityData['contact_person'] = $org->name;

            Opportunity::create($opportunityData);
        }

        $this->command->info('Sample opportunities created successfully!');
    }
}
