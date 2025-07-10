<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\ApplicationTaskStatus;
use Carbon\Carbon;

class SampleReportsDataSeeder extends Seeder
{
    public function run()
    {
        // Get roles
        $organizationRole = Role::where('name', 'organization')->first();
        $volunteerRole = Role::where('name', 'volunteer')->first();

        if (!$organizationRole || !$volunteerRole) {
            $this->command->error('Roles not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Find or create an organization user
        $organization = User::whereHas('roles', function($query) use ($organizationRole) {
            $query->where('role_id', $organizationRole->id);
        })->first();

        if (!$organization) {
            $organization = User::create([
                'name' => 'Sample Organization',
                'email' => 'org@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
            $organization->roles()->attach($organizationRole->id);
        }

        // Find or create volunteer users
        $volunteers = [];
        for ($i = 1; $i <= 5; $i++) {
            $volunteer = User::where('email', "volunteer{$i}@example.com")->first();
            if (!$volunteer) {
                $volunteer = User::create([
                    'name' => "Volunteer {$i}",
                    'email' => "volunteer{$i}@example.com",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now()
                ]);
                $volunteer->roles()->attach($volunteerRole->id);
            }
            $volunteers[] = $volunteer;
        }

        // Create sample opportunities
        $opportunities = [];
        for ($i = 1; $i <= 3; $i++) {
            $opportunity = Opportunity::create([
                'title' => "Sample Opportunity {$i}",
                'description' => "This is a sample opportunity for testing reports functionality. Opportunity {$i} involves community service work.",
                'location' => "Location {$i}",
                'start_date' => Carbon::now()->subDays(30 + $i),
                'end_date' => Carbon::now()->subDays(10 + $i),
                'volunteers_needed' => 3,
                'organization_id' => $organization->id,
                'status' => 'active'
            ]);
            $opportunities[] = $opportunity;
        }

        // Create applications and task statuses
        foreach ($opportunities as $index => $opportunity) {
            foreach ($volunteers as $volIndex => $volunteer) {
                // Only create applications for some volunteers to simulate realistic data
                if ($volIndex <= 2) { // First 3 volunteers apply to each opportunity
                    $application = Application::create([
                        'volunteer_id' => $volunteer->id,
                        'opportunity_id' => $opportunity->id,
                        'status' => 'accepted',
                        'applied_at' => Carbon::now()->subDays(25 + $index),
                        'responded_at' => Carbon::now()->subDays(24 + $index)
                    ]);

                    // Create task status for completed work
                    $startDate = Carbon::now()->subDays(20 + $index);
                    $completedDate = $startDate->copy()->addDays(rand(3, 10));
                    
                    ApplicationTaskStatus::create([
                        'application_id' => $application->id,
                        'status' => 'completed',
                        'started_at' => $startDate,
                        'completed_at' => $completedDate,
                        'completion_notes' => "Completed work for {$opportunity->title}. All tasks were finished successfully and the community benefited greatly from this volunteer work.",
                        'work_evidence' => [
                            'photos' => ['photo1.jpg', 'photo2.jpg'],
                            'documents' => ['completion_report.pdf']
                        ]
                    ]);

                    // Add feedback ratings
                    $application->update([
                        'feedback_rating' => rand(4, 5),
                        'feedback_comment' => "Excellent work by {$volunteer->name}. Very dedicated and professional."
                    ]);
                }
            }
        }

        // Create one more opportunity with in-progress work
        $inProgressOpportunity = Opportunity::create([
            'title' => 'Ongoing Community Project',
            'description' => 'This is an ongoing community project with volunteers currently working.',
            'location' => 'Community Center',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(20),
            'volunteers_needed' => 2,
            'organization_id' => $organization->id,
            'status' => 'active'
        ]);

        // Add in-progress applications
        foreach (array_slice($volunteers, 0, 2) as $volunteer) {
            $application = Application::create([
                'volunteer_id' => $volunteer->id,
                'opportunity_id' => $inProgressOpportunity->id,
                'status' => 'accepted',
                'applied_at' => Carbon::now()->subDays(12),
                'responded_at' => Carbon::now()->subDays(11)
            ]);

            ApplicationTaskStatus::create([
                'application_id' => $application->id,
                'status' => 'in_progress',
                'started_at' => Carbon::now()->subDays(8),
                'completion_notes' => 'Work is progressing well. About 60% complete.',
                'work_evidence' => [
                    'progress_photos' => ['progress1.jpg', 'progress2.jpg']
                ]
            ]);
        }

        $this->command->info('Sample reports data created successfully!');
        $this->command->info("Organization: {$organization->email}");
        $this->command->info("Volunteers: " . implode(', ', array_map(fn($v) => $v->email, $volunteers)));
        $this->command->info("Opportunities created: " . (count($opportunities) + 1));
        $this->command->info("Completed tasks: " . (count($opportunities) * 3));
        $this->command->info("In-progress tasks: 2");
    }
}
