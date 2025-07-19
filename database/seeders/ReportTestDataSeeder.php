<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\OrganizationProfile;
use App\Models\VolunteerProfile;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\Task;
use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ReportTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $organizationRole = Role::firstOrCreate(['name' => 'organization']);
        $volunteerRole = Role::firstOrCreate(['name' => 'volunteer']);

        // Create test organization user
        $orgUser = User::create([
            'name' => 'Test Organization',
            'email' => 'org@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'account_status' => 'active',
        ]);
        $orgUser->roles()->attach($organizationRole->id);

        // Create organization profile
        OrganizationProfile::create([
            'user_id' => $orgUser->id,
            'org_name' => 'Test Community Organization',
            'description' => 'A test organization for report generation',
            'mission' => 'To serve the community through volunteer coordination',
            'sector' => 'Community Development',
            'org_type' => 'NGO',
            'physical_address' => 'Test Address, Lilongwe',
            'district' => 'Lilongwe',
            'region' => 'Central',
            'email' => 'org@test.com',
            'phone' => '+265 123 456 789',
            'focus_areas' => ['Community Development', 'Education', 'Health'],
            'contact_person_name' => 'Test Manager',
            'contact_person_email' => 'manager@test.com',
            'is_complete' => true,
            'profile_completed_at' => now(),
            'status' => 'approved'
        ]);

        // Create test volunteer users
        $volunteers = [];
        for ($i = 1; $i <= 10; $i++) {
            $volunteer = User::create([
                'name' => "Volunteer {$i}",
                'email' => "volunteer{$i}@test.com",
                'password' => Hash::make('password'),
                'is_active' => true,
                'account_status' => 'active',
            ]);
            $volunteer->roles()->attach($volunteerRole->id);

            // Create volunteer profile
            VolunteerProfile::create([
                'user_id' => $volunteer->id,
                'full_name' => "Volunteer {$i}",
                'phone' => "+265 99{$i} 123 456",
                'bio' => "Test volunteer {$i} profile for report generation",
                'physical_address' => "Test Address {$i}, Lilongwe",
                'district' => 'Lilongwe',
                'region' => 'Central',
                'education_level' => 'Degree',
                'motivation' => 'I want to help my community',
                'skills' => ['Teaching', 'Technology', 'Healthcare'],
                'available_days' => ['monday', 'tuesday', 'wednesday'],
                'availability_type' => 'flexible',
                'is_complete' => true,
                'profile_completed_at' => now()
            ]);

            $volunteers[] = $volunteer;
        }

        // Create test opportunities
        $opportunities = [];
        for ($i = 1; $i <= 3; $i++) {
            $opportunity = Opportunity::create([
                'organization_id' => $orgUser->id,
                'title' => "Test Opportunity {$i}",
                'description' => "Test opportunity {$i} for report generation",
                'requirements' => 'Basic skills required',
                'benefits' => 'Community service experience',
                'category' => 'Community Development',
                'type' => 'one-time',
                'urgency' => 'medium',
                'location_type' => 'on-site',
                'address' => 'Test Location, Lilongwe',
                'district' => 'Lilongwe',
                'region' => 'Central',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(30),
                'volunteers_needed' => 5,
                'volunteers_recruited' => 0,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(30),
            ]);
            $opportunities[] = $opportunity;
        }

        // Create applications for current month
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        foreach ($volunteers as $index => $volunteer) {
            foreach ($opportunities as $oppIndex => $opportunity) {
                if ($index < 7) { // First 7 volunteers apply to opportunities
                    $application = Application::create([
                        'opportunity_id' => $opportunity->id,
                        'volunteer_id' => $volunteer->id,
                        'message' => "Application message from {$volunteer->name}",
                        'relevant_experience' => 'Previous volunteer experience in community work',
                        'agrees_to_terms' => true,
                        'status' => 'accepted',
                        'applied_at' => $index < 4 ? $currentMonth->copy()->subDays(rand(1, 15)) : $lastMonth->copy()->subDays(rand(1, 15)),
                        'accepted_at' => $index < 4 ? $currentMonth->copy()->subDays(rand(1, 10)) : $lastMonth->copy()->subDays(rand(1, 10)),
                    ]);

                    // Create tasks for each opportunity
                    $task = Task::create([
                        'opportunity_id' => $opportunity->id,
                        'created_by' => $orgUser->id,
                        'title' => "Task for {$opportunity->title} - Volunteer " . ($index + 1),
                        'description' => "Test task for volunteer {$volunteer->name}",
                        'priority' => ['low', 'medium', 'high', 'urgent'][rand(0, 3)],
                        'status' => 'published',
                        'start_datetime' => $currentMonth->copy()->addDays(rand(1, 10)),
                        'end_datetime' => $currentMonth->copy()->addDays(rand(11, 20)),
                        'volunteers_needed' => 1,
                        'volunteers_assigned' => 1,
                    ]);

                    // Create assignments
                    $assignmentStatus = ['completed', 'cancelled', 'no_show', 'declined'][rand(0, 3)];
                    if ($index < 4) { // More completed tasks for current month
                        $assignmentStatus = rand(0, 1) ? 'completed' : 'cancelled';
                    }

                    $assignment = Assignment::create([
                        'task_id' => $task->id,
                        'volunteer_id' => $volunteer->id,
                        'assigned_by' => $orgUser->id,
                        'status' => $assignmentStatus,
                        'assignment_method' => 'manual',
                        'assigned_at' => $index < 4 ? $currentMonth->copy()->subDays(rand(1, 10)) : $lastMonth->copy()->subDays(rand(1, 10)),
                        'scheduled_start' => $currentMonth->copy()->addDays(rand(1, 10)),
                        'scheduled_end' => $currentMonth->copy()->addDays(rand(11, 20)),
                    ]);

                    // Set completion data for completed tasks
                    if ($assignmentStatus === 'completed') {
                        $assignment->update([
                            'accepted_at' => $assignment->assigned_at->addHours(2),
                            'completed_at' => $index < 4 ? $currentMonth->copy()->subDays(rand(1, 5)) : $lastMonth->copy()->subDays(rand(1, 5)),
                            'actual_start' => $assignment->scheduled_start,
                            'actual_end' => $assignment->scheduled_end,
                            'performance_rating' => rand(3, 5),
                            'performance_notes' => 'Good performance on the task',
                            'task_completed_successfully' => true,
                            'checked_in_at' => $assignment->scheduled_start,
                            'checked_out_at' => $assignment->scheduled_end,
                        ]);
                    } elseif ($assignmentStatus === 'declined') {
                        $assignment->update([
                            'declined_at' => $assignment->assigned_at->addHours(1),
                            'decline_reason' => 'Schedule conflict',
                        ]);
                    }

                    break; // Each volunteer applies to only one opportunity for simplicity
                }
            }
        }

        $this->command->info('Report test data seeded successfully!');
        $this->command->info('Organization: org@test.com / password');
        $this->command->info('Volunteers: volunteer1@test.com to volunteer10@test.com / password');
    }
}
