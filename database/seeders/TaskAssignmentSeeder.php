<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\Task;
use App\Models\ApplicationTaskStatus;
use App\Services\TaskAssignmentService;

class TaskAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating task assignment test data...\n";

        // Get or create roles
        $volunteerRole = Role::firstOrCreate(['name' => 'volunteer']);
        $organizationRole = Role::firstOrCreate(['name' => 'organization']);

        // Get existing users or create them
        $volunteer = User::whereHas('roles', function($q) {
            $q->where('name', 'volunteer');
        })->first();

        $organization = User::whereHas('roles', function($q) {
            $q->where('name', 'organization');
        })->first();

        if (!$volunteer) {
            $volunteer = User::create([
                'name' => 'Test Volunteer',
                'email' => 'volunteer@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
            $volunteer->roles()->attach($volunteerRole);
        }

        if (!$organization) {
            $organization = User::create([
                'name' => 'Test Organization',
                'email' => 'organization@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
            $organization->roles()->attach($organizationRole);
        }

        // Create or get opportunity
        $opportunity = Opportunity::first();
        if (!$opportunity) {
            $opportunity = Opportunity::create([
                'organization_id' => $organization->id,
                'title' => 'Community Clean-up Drive',
                'description' => 'Help us clean up the local park and make our community beautiful.',
                'location' => 'Central Park',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(8),
                'volunteers_needed' => 10,
                'status' => 'active'
            ]);
        }

        // Create tasks for the opportunity if they don't exist
        $existingTasks = Task::where('opportunity_id', $opportunity->id)->count();
        if ($existingTasks == 0) {
            $tasks = [
                [
                    'title' => 'Litter Collection',
                    'description' => 'Collect and sort litter throughout the park area. Gloves and bags will be provided.',
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                ],
                [
                    'title' => 'Garden Maintenance',
                    'description' => 'Weed flower beds, plant new flowers, and water existing plants.',
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                ],
                [
                    'title' => 'Equipment Setup',
                    'description' => 'Set up tables, chairs, and equipment for the volunteer coordination area.',
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                ]
            ];

            foreach ($tasks as $taskData) {
                Task::create([
                    'opportunity_id' => $opportunity->id,
                    'title' => $taskData['title'],
                    'description' => $taskData['description'],
                    'start_date' => $taskData['start_date'],
                    'end_date' => $taskData['end_date'],
                    'status' => 'active',
                    'assigned_volunteers' => 0
                ]);
            }
            echo "Created 3 tasks for the opportunity.\n";
        }

        // Create application if it doesn't exist
        $application = Application::where('volunteer_id', $volunteer->id)
            ->where('opportunity_id', $opportunity->id)
            ->first();

        if (!$application) {
            $application = Application::create([
                'volunteer_id' => $volunteer->id,
                'opportunity_id' => $opportunity->id,
                'status' => 'accepted',
                'confirmation_status' => 'confirmed',
                'applied_at' => now()->subDays(2),
                'responded_at' => now()->subDays(1)
            ]);
            echo "Created application for volunteer.\n";
        }

        // Assign task if not already assigned
        if (!$application->task_id) {
            $taskAssignmentService = new TaskAssignmentService();
            $success = $taskAssignmentService->autoAssignTasksToVolunteer($application);
            
            if ($success) {
                echo "Successfully assigned task to volunteer.\n";
                
                // Create task status
                ApplicationTaskStatus::updateOrCreate(
                    ['application_id' => $application->id],
                    ['status' => 'pending']
                );
                echo "Created task status record.\n";
            } else {
                echo "Failed to assign task to volunteer.\n";
            }
        } else {
            echo "Task already assigned to volunteer.\n";
        }

        // Create additional test scenarios
        $this->createAdditionalTestData($volunteer, $organization, $opportunity);

        echo "Task assignment seeder completed!\n";
        echo "You can now:\n";
        echo "1. Login as volunteer (volunteer@test.com / password)\n";
        echo "2. Visit /volunteer/tasks to see assigned tasks\n";
        echo "3. Test task management features\n";
    }

    private function createAdditionalTestData($volunteer, $organization, $opportunity)
    {
        // Create a second opportunity with different tasks
        $opportunity2 = Opportunity::where('title', 'Food Bank Assistance')->first();
        if (!$opportunity2) {
            $opportunity2 = Opportunity::create([
                'organization_id' => $organization->id,
                'title' => 'Food Bank Assistance',
                'description' => 'Help sort and distribute food to families in need.',
                'location' => 'Community Food Bank',
                'start_date' => now()->addDays(14),
                'end_date' => now()->addDays(15),
                'volunteers_needed' => 8,
                'status' => 'active'
            ]);

            // Create task for second opportunity
            $task2 = Task::create([
                'opportunity_id' => $opportunity2->id,
                'title' => 'Food Sorting and Packaging',
                'description' => 'Sort donated food items and package them for distribution to families.',
                'start_date' => $opportunity2->start_date,
                'end_date' => $opportunity2->end_date,
                'status' => 'active',
                'assigned_volunteers' => 0
            ]);

            // Create second application
            $application2 = Application::create([
                'volunteer_id' => $volunteer->id,
                'opportunity_id' => $opportunity2->id,
                'status' => 'accepted',
                'confirmation_status' => 'confirmed',
                'applied_at' => now()->subDays(1),
                'responded_at' => now()
            ]);

            // Assign task
            $taskAssignmentService = new TaskAssignmentService();
            $taskAssignmentService->autoAssignTasksToVolunteer($application2);

            // Create task status with in_progress status
            ApplicationTaskStatus::create([
                'application_id' => $application2->id,
                'status' => 'in_progress',
                'started_at' => now()->subHours(2)
            ]);

            echo "Created second opportunity with in-progress task.\n";
        }
    }
}
