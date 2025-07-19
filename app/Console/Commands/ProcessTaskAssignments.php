<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Assignment;
use App\Services\TaskAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessTaskAssignments extends Command
{
    protected $signature = 'tasks:process-assignments
                          {--send-reminders : Send task reminders to volunteers}
                          {--handle-overdue : Handle overdue tasks}
                          {--auto-assign : Auto-assign volunteers to tasks}';

    protected $description = 'Process task assignments, send reminders, and handle overdue tasks';

    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        parent::__construct();
        $this->taskAssignmentService = $taskAssignmentService;
    }

    public function handle()
    {
        $this->info('Starting task assignment processing...');

        if ($this->option('send-reminders')) {
            $this->sendTaskReminders();
        }

        if ($this->option('handle-overdue')) {
            $this->handleOverdueTasks();
        }

        if ($this->option('auto-assign')) {
            $this->autoAssignTasks();
        }

        // If no specific options, run all processes
        if (!$this->option('send-reminders') && !$this->option('handle-overdue') && !$this->option('auto-assign')) {
            $this->sendTaskReminders();
            $this->handleOverdueTasks();
            $this->autoAssignTasks();
        }

        $this->info('Task assignment processing completed.');
    }

    private function sendTaskReminders()
    {
        $this->info('Sending task reminders...');

        try {
            // Get assignments that need reminders (24 hours before start time)
            $upcomingAssignments = Assignment::where('status', 'accepted')
                ->where('reminder_sent', false)
                ->whereBetween('scheduled_start', [now()->addHours(23), now()->addHours(25)])
                ->with(['volunteer', 'task.opportunity'])
                ->get();

            $reminderCount = 0;

            foreach ($upcomingAssignments as $assignment) {
                try {
                    $assignment->volunteer->notify(new \App\Notifications\TaskReminderNotification($assignment));
                    $assignment->update(['reminder_sent' => true]);
                    $reminderCount++;

                    $this->line("✓ Reminder sent to {$assignment->volunteer->name} for task: {$assignment->task->title}");

                } catch (\Exception $e) {
                    $this->error("✗ Failed to send reminder for assignment {$assignment->id}: {$e->getMessage()}");
                    Log::error("Failed to send task reminder", [
                        'assignment_id' => $assignment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("Sent {$reminderCount} task reminders.");

        } catch (\Exception $e) {
            $this->error("Error in sendTaskReminders: {$e->getMessage()}");
            Log::error("Error in sendTaskReminders command", ['error' => $e->getMessage()]);
        }
    }

    private function handleOverdueTasks()
    {
        $this->info('Handling overdue tasks...');

        try {
            $overdueTasks = Task::where('status', 'active')
                ->where('end_datetime', '<', now()->subHours(2))
                ->with(['assignments' => function($query) {
                    $query->where('status', 'accepted')
                          ->whereNull('completed_at');
                }])
                ->get();

            $overdueCount = 0;

            foreach ($overdueTasks as $task) {
                $incompleteAssignments = 0;

                foreach ($task->assignments as $assignment) {
                    if (!$assignment->checked_out_at) {
                        $assignment->update([
                            'status' => 'incomplete',
                            'volunteer_feedback' => 'Task marked as incomplete due to no check-out'
                        ]);
                        $incompleteAssignments++;
                    }
                }

                if ($incompleteAssignments > 0) {
                    // Notify organization about overdue task
                    try {
                        $task->opportunity->organization->notify(
                            new \App\Notifications\TaskOverdueNotification($task)
                        );
                    } catch (\Exception $e) {
                        $this->error("Failed to notify organization about overdue task {$task->id}");
                    }

                    $this->line("✓ Handled overdue task: {$task->title} ({$incompleteAssignments} incomplete assignments)");
                    $overdueCount++;
                }
            }

            $this->info("Handled {$overdueCount} overdue tasks.");

        } catch (\Exception $e) {
            $this->error("Error in handleOverdueTasks: {$e->getMessage()}");
            Log::error("Error in handleOverdueTasks command", ['error' => $e->getMessage()]);
        }
    }

    private function autoAssignTasks()
    {
        $this->info('Auto-assigning volunteers to tasks...');

        try {
            // Get tasks that need auto-assignment
            $tasksNeedingAssignment = Task::where('status', 'active')
                ->where('assignment_type', 'auto')
                ->whereColumn('volunteers_assigned', '<', 'volunteers_needed')
                ->where('assignment_deadline', '>', now())
                ->get();

            $assignmentCount = 0;

            foreach ($tasksNeedingAssignment as $task) {
                try {
                    $assigned = $this->taskAssignmentService->autoAssignVolunteersToTask($task);
                    if ($assigned) {
                        $this->line("✓ Auto-assigned volunteers to task: {$task->title}");
                        $assignmentCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Failed to auto-assign task {$task->id}: {$e->getMessage()}");
                }
            }

            $this->info("Auto-assigned volunteers to {$assignmentCount} tasks.");

        } catch (\Exception $e) {
            $this->error("Error in autoAssignTasks: {$e->getMessage()}");
            Log::error("Error in autoAssignTasks command", ['error' => $e->getMessage()]);
        }
    }
}
