<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\Application;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'organization']);
        Role::create(['name' => 'volunteer']);
    }

    /**
     * Test task creation with opportunity
     */
    public function test_organization_can_create_opportunity_with_tasks(): void
    {
        // Create organization user
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        // Create opportunity with tasks
        $response = $this->actingAs($organization, 'sanctum')
            ->postJson('/api/opportunities', [
                'title' => 'Test Opportunity',
                'description' => 'Test Description',
                'location' => 'Test Location',
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'volunteers_needed' => 5,
                'tasks' => [
                    [
                        'title' => 'Task 1',
                        'description' => 'First task description',
                        'start_date' => now()->addDays(2)->toDateString(),
                        'end_date' => now()->addDays(10)->toDateString(),
                    ],
                    [
                        'title' => 'Task 2',
                        'description' => 'Second task description',
                        'start_date' => now()->addDays(11)->toDateString(),
                        'end_date' => now()->addDays(20)->toDateString(),
                    ]
                ]
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'title',
            'tasks' => [
                '*' => ['id', 'title', 'description', 'start_date', 'end_date']
            ]
        ]);

        // Verify tasks were created
        $opportunity = Opportunity::first();
        $this->assertEquals(2, $opportunity->tasks()->count());
    }

    /**
     * Test recruitment closure when volunteer limit is reached
     */
    public function test_recruitment_closes_when_volunteer_limit_reached(): void
    {
        // Create organization and opportunity
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        $opportunity = Opportunity::create([
            'organization_id' => $organization->id,
            'title' => 'Test Opportunity',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'volunteers_needed' => 2,
            'status' => 'active'
        ]);

        // Create volunteers
        $volunteer1 = User::factory()->create();
        $volunteer2 = User::factory()->create();
        $volunteer3 = User::factory()->create();
        $volRole = Role::where('name', 'volunteer')->first();
        $volunteer1->roles()->attach($volRole);
        $volunteer2->roles()->attach($volRole);
        $volunteer3->roles()->attach($volRole);

        // Create applications
        $app1 = Application::create([
            'volunteer_id' => $volunteer1->id,
            'opportunity_id' => $opportunity->id,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        $app2 = Application::create([
            'volunteer_id' => $volunteer2->id,
            'opportunity_id' => $opportunity->id,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        $app3 = Application::create([
            'volunteer_id' => $volunteer3->id,
            'opportunity_id' => $opportunity->id,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        // Accept first application
        $this->actingAs($organization, 'sanctum')
            ->putJson("/api/applications/{$app1->id}/respond", [
                'status' => 'accepted'
            ]);

        // Accept second application (should trigger recruitment closure)
        $this->actingAs($organization, 'sanctum')
            ->putJson("/api/applications/{$app2->id}/respond", [
                'status' => 'accepted'
            ]);

        // Verify opportunity status changed and third application was rejected
        $opportunity->refresh();
        $app3->refresh();

        $this->assertEquals('recruitment_closed', $opportunity->status);
        $this->assertEquals('rejected', $app3->status);
    }

    /**
     * Test task expiration command
     */
    public function test_expired_tasks_are_automatically_completed(): void
    {
        // Create organization and opportunity
        $organization = User::factory()->create();
        $orgRole = Role::where('name', 'organization')->first();
        $organization->roles()->attach($orgRole);

        $opportunity = Opportunity::create([
            'organization_id' => $organization->id,
            'title' => 'Test Opportunity',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(10),
            'volunteers_needed' => 2,
            'status' => 'active'
        ]);

        // Create expired task
        $task = Task::create([
            'opportunity_id' => $opportunity->id,
            'title' => 'Expired Task',
            'description' => 'This task should be expired',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(1), // Expired yesterday
            'status' => 'active'
        ]);

        // Run the command
        $this->artisan('tasks:auto-complete-expired')
            ->expectsOutput('Completed task: Expired Task (ID: 1)')
            ->expectsOutput('Successfully completed 1 expired tasks.');

        // Verify task was completed
        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }
}
