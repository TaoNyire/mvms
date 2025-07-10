<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\ApplicationTaskStatus;
use Carbon\Carbon;

class TaskStatusSeeder extends Seeder
{
    public function run()
    {
        // Get existing applications
        $applications = Application::where('status', 'accepted')->get();
        
        if ($applications->isEmpty()) {
            $this->command->info('No accepted applications found. Please create some applications first.');
            return;
        }

        $this->command->info("Found {$applications->count()} accepted applications. Creating task status data...");

        foreach ($applications as $index => $application) {
            // Create different types of task status for variety
            $statusType = $index % 4; // 0, 1, 2, 3
            
            switch ($statusType) {
                case 0: // Completed task
                    $startDate = Carbon::now()->subDays(rand(20, 40));
                    $completedDate = $startDate->copy()->addDays(rand(5, 15));
                    
                    ApplicationTaskStatus::create([
                        'application_id' => $application->id,
                        'status' => 'completed',
                        'started_at' => $startDate,
                        'completed_at' => $completedDate,
                        'completion_notes' => "Successfully completed all assigned tasks. The work was done efficiently and met all requirements. Great contribution to the community project.",
                        'work_evidence' => [
                            'photos' => ['before_work.jpg', 'during_work.jpg', 'after_work.jpg'],
                            'documents' => ['completion_report.pdf'],
                            'notes' => 'All objectives achieved successfully'
                        ]
                    ]);
                    
                    // Add feedback rating
                    $application->update([
                        'feedback_rating' => rand(4, 5),
                        'feedback_comment' => "Excellent volunteer! Very dedicated and professional in their approach. Would definitely recommend for future opportunities."
                    ]);
                    break;
                    
                case 1: // In progress task
                    $startDate = Carbon::now()->subDays(rand(5, 15));
                    
                    ApplicationTaskStatus::create([
                        'application_id' => $application->id,
                        'status' => 'in_progress',
                        'started_at' => $startDate,
                        'completion_notes' => "Work is progressing well. About 70% complete. Volunteer is very engaged and following all guidelines properly.",
                        'work_evidence' => [
                            'progress_photos' => ['progress_1.jpg', 'progress_2.jpg'],
                            'notes' => 'Good progress so far, on track for completion'
                        ]
                    ]);
                    break;
                    
                case 2: // Another completed task with different timeline
                    $startDate = Carbon::now()->subDays(rand(30, 60));
                    $completedDate = $startDate->copy()->addDays(rand(3, 8));
                    
                    ApplicationTaskStatus::create([
                        'application_id' => $application->id,
                        'status' => 'completed',
                        'started_at' => $startDate,
                        'completed_at' => $completedDate,
                        'completion_notes' => "Task completed ahead of schedule. Volunteer showed great initiative and helped other team members as well. Outstanding performance.",
                        'work_evidence' => [
                            'photos' => ['final_result.jpg', 'team_photo.jpg'],
                            'documents' => ['impact_report.pdf'],
                            'testimonials' => ['Great team player and very reliable']
                        ]
                    ]);
                    
                    // Add feedback rating
                    $application->update([
                        'feedback_rating' => 5,
                        'feedback_comment' => "Outstanding volunteer! Completed work ahead of schedule and helped others. Highly recommended!"
                    ]);
                    break;
                    
                case 3: // Pending task (no task status created)
                    // Leave as pending - no task status record
                    break;
            }
        }

        // Create some additional completed tasks from older dates for trend analysis
        $olderApplications = $applications->take(3);
        foreach ($olderApplications as $application) {
            // Create older completed task for monthly trends
            $startDate = Carbon::now()->subDays(rand(60, 90));
            $completedDate = $startDate->copy()->addDays(rand(7, 14));
            
            ApplicationTaskStatus::create([
                'application_id' => $application->id,
                'status' => 'completed',
                'started_at' => $startDate,
                'completed_at' => $completedDate,
                'completion_notes' => "Historical completed task for trend analysis. Work was completed successfully with positive community impact.",
                'work_evidence' => [
                    'photos' => ['historical_work.jpg'],
                    'notes' => 'Completed as part of earlier community initiative'
                ]
            ]);
        }

        $completedCount = ApplicationTaskStatus::where('status', 'completed')->count();
        $inProgressCount = ApplicationTaskStatus::where('status', 'in_progress')->count();
        $pendingCount = $applications->count() - ApplicationTaskStatus::count();

        $this->command->info('Task status data created successfully!');
        $this->command->info("Completed tasks: {$completedCount}");
        $this->command->info("In progress tasks: {$inProgressCount}");
        $this->command->info("Pending tasks: {$pendingCount}");
        $this->command->info('Organizations can now view real data in their reports!');
    }
}
