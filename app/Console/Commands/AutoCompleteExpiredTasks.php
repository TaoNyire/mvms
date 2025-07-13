<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Notifications\TaskCompletedNotification;

class AutoCompleteExpiredTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:auto-complete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically complete tasks that have passed their end date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredTasks = Task::expired()->get();

        if ($expiredTasks->isEmpty()) {
            $this->info('No expired tasks found.');
            return;
        }

        $completedCount = 0;

        foreach ($expiredTasks as $task) {
            // Mark task as completed
            $task->markAsCompleted('Automatically completed due to end date reached');

            // Notify all volunteers working on this task
            $applications = $task->applications()
                                ->where('status', 'accepted')
                                ->where('confirmation_status', 'confirmed')
                                ->with('volunteer')
                                ->get();

            foreach ($applications as $application) {
                $application->volunteer->notify(new TaskCompletedNotification($task));
            }

            $completedCount++;
            $this->info("Completed task: {$task->title} (ID: {$task->id})");
        }

        $this->info("Successfully completed {$completedCount} expired tasks.");
    }
}
