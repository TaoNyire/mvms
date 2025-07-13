<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Application;
use App\Models\ApplicationTaskStatus;
use App\Notifications\VolunteerAssignedToTaskNotification;
use Illuminate\Support\Facades\Log;

class TaskAssignmentService
{
    /**
     * Automatically assign tasks to a volunteer when their application is accepted
     */
    public function autoAssignTasksToVolunteer(Application $application)
    {
        try {
            // Get all active tasks for this opportunity
            $activeTasks = $application->opportunity->tasks()
                ->where('status', 'active')
                ->get();

            if ($activeTasks->isEmpty()) {
                Log::info("No active tasks found for opportunity", [
                    'opportunity_id' => $application->opportunity_id,
                    'application_id' => $application->id
                ]);
                return false;
            }

            foreach ($activeTasks as $task) {
                // Check if the volunteer is not already assigned to this task
                if ($application->task_id !== $task->id) {
                    $this->assignTaskToVolunteer($application, $task);
                    
                    // For now, assign only the first active task
                    // You can modify this logic to assign multiple tasks if needed
                    break;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to auto-assign tasks to volunteer", [
                'application_id' => $application->id,
                'volunteer_id' => $application->volunteer->id,
                'opportunity_id' => $application->opportunity_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Assign a specific task to a volunteer
     */
    public function assignTaskToVolunteer(Application $application, Task $task)
    {
        try {
            // Assign the task to the application
            $application->update(['task_id' => $task->id]);

            // Create an ApplicationTaskStatus record for tracking
            ApplicationTaskStatus::updateOrCreate(
                ['application_id' => $application->id],
                ['status' => 'pending']
            );

            // Update the task's assigned volunteers count
            $task->increment('assigned_volunteers');

            // Notify the volunteer about the task assignment
            $application->volunteer->notify(new VolunteerAssignedToTaskNotification($task));

            // Log the assignment for debugging
            Log::info("Task assigned to volunteer", [
                'volunteer_id' => $application->volunteer->id,
                'volunteer_name' => $application->volunteer->name,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'opportunity_title' => $application->opportunity->title,
                'application_id' => $application->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to assign task to volunteer", [
                'application_id' => $application->id,
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Assign multiple tasks to a volunteer
     */
    public function assignMultipleTasksToVolunteer(Application $application, array $taskIds)
    {
        $successCount = 0;
        $tasks = Task::whereIn('id', $taskIds)->get();

        foreach ($tasks as $task) {
            if ($this->assignTaskToVolunteer($application, $task)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Reassign a volunteer from one task to another
     */
    public function reassignVolunteerTask(Application $application, Task $fromTask, Task $toTask)
    {
        try {
            // Remove from old task
            if ($application->task_id === $fromTask->id) {
                $application->update(['task_id' => null]);
                $fromTask->decrement('assigned_volunteers');
            }

            // Assign to new task
            $this->assignTaskToVolunteer($application, $toTask);

            Log::info("Volunteer reassigned between tasks", [
                'application_id' => $application->id,
                'volunteer_id' => $application->volunteer->id,
                'from_task_id' => $fromTask->id,
                'to_task_id' => $toTask->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to reassign volunteer task", [
                'application_id' => $application->id,
                'from_task_id' => $fromTask->id,
                'to_task_id' => $toTask->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove task assignment from volunteer
     */
    public function removeTaskAssignment(Application $application)
    {
        try {
            if ($application->task_id) {
                $task = $application->task;
                $application->update(['task_id' => null]);
                
                if ($task) {
                    $task->decrement('assigned_volunteers');
                }

                // Update task status to indicate removal
                $taskStatus = $application->taskStatus;
                if ($taskStatus) {
                    $taskStatus->update([
                        'status' => 'quit',
                        'completion_notes' => 'Task assignment removed by organization'
                    ]);
                }

                Log::info("Task assignment removed from volunteer", [
                    'application_id' => $application->id,
                    'volunteer_id' => $application->volunteer->id,
                    'task_id' => $task->id ?? null
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to remove task assignment", [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Auto-assign tasks to all confirmed volunteers for an opportunity
     */
    public function autoAssignTasksToAllVolunteers($opportunityId)
    {
        try {
            $confirmedApplications = Application::where('opportunity_id', $opportunityId)
                ->where('status', 'accepted')
                ->where('confirmation_status', 'confirmed')
                ->whereNull('task_id')
                ->get();

            $successCount = 0;
            foreach ($confirmedApplications as $application) {
                if ($this->autoAssignTasksToVolunteer($application)) {
                    $successCount++;
                }
            }

            Log::info("Auto-assigned tasks to volunteers for opportunity", [
                'opportunity_id' => $opportunityId,
                'total_volunteers' => $confirmedApplications->count(),
                'successful_assignments' => $successCount
            ]);

            return $successCount;
        } catch (\Exception $e) {
            Log::error("Failed to auto-assign tasks to all volunteers", [
                'opportunity_id' => $opportunityId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get task assignment statistics for an opportunity
     */
    public function getTaskAssignmentStats($opportunityId)
    {
        $totalVolunteers = Application::where('opportunity_id', $opportunityId)
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->count();

        $assignedVolunteers = Application::where('opportunity_id', $opportunityId)
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->whereNotNull('task_id')
            ->count();

        $unassignedVolunteers = $totalVolunteers - $assignedVolunteers;

        return [
            'total_volunteers' => $totalVolunteers,
            'assigned_volunteers' => $assignedVolunteers,
            'unassigned_volunteers' => $unassignedVolunteers,
            'assignment_percentage' => $totalVolunteers > 0 ? round(($assignedVolunteers / $totalVolunteers) * 100, 2) : 0
        ];
    }
}
