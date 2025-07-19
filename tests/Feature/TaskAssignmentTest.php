<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\Task;
use App\Models\ApplicationTaskStatus;
use App\Services\TaskAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $volunteer;
    protected $organization;
    protected $opportunity;
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $volunteerRole = Role::create(['name' => 'volunteer']);
        $organizationRole = Role::create(['name' => 'organization']);

        // Create users
        $this->volunteer = User::factory()->create();
        $this->volunteer->roles()->attach($volunteerRole);

        $this->organization = User::factory()->create();
        $this->organization->roles()->attach($organizationRole);

        // Create opportunity
        $this->opportunity = Opportunity::create([
            'organization_id' => $this->organization->id,
            'title' => 'Test Opportunity',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(14),
            'volunteers_needed' => 5,
            'status' => 'active'
        ]);

        // Create task
        $this->task = Task::create([
            'opportunity_id' => $this->opportunity->id,
            'title' => 'Test Task',
            'description' => 'Test Task Description',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(14),
            'status' => 'active'
        ]);
    }

    /** @test */
    public function volunteer_gets_task_assigned_when_application_is_accepted()
    {
        // Create application
        $application = Application::create([
            'volunteer_id' => $this->volunteer->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        // Accept the application (simulating organization response)
        $response = $this->actingAs($this->organization)
            ->putJson("/api/applications/{$application->id}/respond", [
                'status' => 'accepted'
            ]);

        $response->assertStatus(200);

        // Refresh application from database
        $application->refresh();

        // Assert task was assigned
        $this->assertNotNull($application->task_id);
        $this->assertEquals($this->task->id, $application->task_id);
        $this->assertEquals('accepted', $application->status);
        $this->assertEquals('confirmed', $application->confirmation_status);

        // Assert ApplicationTaskStatus was created
        $taskStatus = ApplicationTaskStatus::where('application_id', $application->id)->first();
        $this->assertNotNull($taskStatus);
        $this->assertEquals('pending', $taskStatus->status);

        // Assert task assigned volunteers count was updated
        $this->task->refresh();
        $this->assertEquals(1, $this->task->assigned_volunteers);
    }

    /** @test */
    public function task_assignment_service_assigns_tasks_correctly()
    {
        // Create application
        $application = Application::create([
            'volunteer_id' => $this->volunteer->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => 'accepted',
            'confirmation_status' => 'confirmed',
            'applied_at' => now(),
            'responded_at' => now()
        ]);

        $taskAssignmentService = new TaskAssignmentService();
        $result = $taskAssignmentService->autoAssignTasksToVolunteer($application);

        $this->assertTrue($result);

        // Refresh application
        $application->refresh();

        // Assert task was assigned
        $this->assertNotNull($application->task_id);
        $this->assertEquals($this->task->id, $application->task_id);

        // Assert ApplicationTaskStatus was created
        $taskStatus = ApplicationTaskStatus::where('application_id', $application->id)->first();
        $this->assertNotNull($taskStatus);
        $this->assertEquals('pending', $taskStatus->status);
    }

    /** @test */
    public function organization_can_track_task_progress()
    {
        // Create application with task assignment
        $application = Application::create([
            'volunteer_id' => $this->volunteer->id,
            'opportunity_id' => $this->opportunity->id,
            'task_id' => $this->task->id,
            'status' => 'accepted',
            'confirmation_status' => 'confirmed',
            'applied_at' => now(),
            'responded_at' => now()
        ]);

        // Create task status
        ApplicationTaskStatus::create([
            'application_id' => $application->id,
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        // Test task progress endpoint
        $response = $this->actingAs($this->organization)
            ->getJson("/api/tasks/{$this->task->id}/progress");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'task',
            'volunteers' => [
                '*' => [
                    'application_id',
                    'volunteer',
                    'task_status',
                    'applied_at',
                    'responded_at'
                ]
            ],
            'total_volunteers',
            'progress_summary'
        ]);

        $responseData = $response->json();
        $this->assertEquals(1, $responseData['total_volunteers']);
        $this->assertEquals(1, $responseData['progress_summary']['in_progress']);
    }

    /** @test */
    public function organization_can_update_volunteer_task_status()
    {
        // Create application with task assignment
        $application = Application::create([
            'volunteer_id' => $this->volunteer->id,
            'opportunity_id' => $this->opportunity->id,
            'task_id' => $this->task->id,
            'status' => 'accepted',
            'applied_at' => now()
        ]);

        // Create initial task status
        ApplicationTaskStatus::create([
            'application_id' => $application->id,
            'status' => 'pending'
        ]);

        // Update task status to completed
        $response = $this->actingAs($this->organization)
            ->putJson("/api/applications/{$application->id}/task-status", [
                'status' => 'completed',
                'completion_notes' => 'Task completed successfully'
            ]);

        $response->assertStatus(200);

        // Verify task status was updated
        $taskStatus = ApplicationTaskStatus::where('application_id', $application->id)->first();
        $this->assertEquals('completed', $taskStatus->status);
        $this->assertEquals('Task completed successfully', $taskStatus->completion_notes);
        $this->assertNotNull($taskStatus->completed_at);
    }

    /** @test */
    public function organization_can_view_opportunity_volunteers_progress()
    {
        // Create multiple volunteers with different task statuses
        $volunteer2 = User::factory()->create();
        $volunteer2->roles()->attach(Role::where('name', 'volunteer')->first());

        $application1 = Application::create([
            'volunteer_id' => $this->volunteer->id,
            'opportunity_id' => $this->opportunity->id,
            'task_id' => $this->task->id,
            'status' => 'accepted',
            'confirmation_status' => 'confirmed',
            'applied_at' => now(),
            'responded_at' => now()
        ]);

        $application2 = Application::create([
            'volunteer_id' => $volunteer2->id,
            'opportunity_id' => $this->opportunity->id,
            'task_id' => $this->task->id,
            'status' => 'accepted',
            'applied_at' => now()
        ]);

        // Create different task statuses
        ApplicationTaskStatus::create([
            'application_id' => $application1->id,
            'status' => 'completed',
            'completed_at' => now()
        ]);

        ApplicationTaskStatus::create([
            'application_id' => $application2->id,
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        // Test opportunity volunteers progress endpoint
        $response = $this->actingAs($this->organization)
            ->getJson("/api/opportunities/{$this->opportunity->id}/volunteers-progress");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'opportunity',
            'volunteers',
            'total_volunteers',
            'progress_summary'
        ]);

        $responseData = $response->json();
        $this->assertEquals(2, $responseData['total_volunteers']);
        $this->assertEquals(1, $responseData['progress_summary']['completed']);
        $this->assertEquals(1, $responseData['progress_summary']['in_progress']);
    }

    /** @test */
    public function task_assignment_stats_are_calculated_correctly()
    {
        // Create multiple applications
        $volunteer2 = User::factory()->create();
        $volunteer2->roles()->attach(Role::where('name', 'volunteer')->first());

        Application::create([
            'volunteer_id' => $this->volunteer->id,
            'opportunity_id' => $this->opportunity->id,
            'task_id' => $this->task->id,
            'status' => 'accepted',
            'confirmation_status' => 'confirmed',
            'applied_at' => now(),
            'responded_at' => now()
        ]);

        Application::create([
            'volunteer_id' => $volunteer2->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => 'accepted',
            'confirmation_status' => 'confirmed',
            'applied_at' => now(),
            'responded_at' => now()
        ]);

        $taskAssignmentService = new TaskAssignmentService();
        $stats = $taskAssignmentService->getTaskAssignmentStats($this->opportunity->id);

        $this->assertEquals(2, $stats['total_volunteers']);
        $this->assertEquals(1, $stats['assigned_volunteers']);
        $this->assertEquals(1, $stats['unassigned_volunteers']);
        $this->assertEquals(50.0, $stats['assignment_percentage']);
    }
}
