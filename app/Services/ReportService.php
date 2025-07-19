<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Assignment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get monthly recruited volunteers for an organization
     */
    public function getMonthlyRecruitedVolunteers($organizationId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return Application::whereHas('opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'accepted')
            ->whereBetween('accepted_at', [$startDate, $endDate])
            ->with([
                'volunteer.volunteerProfile',
                'opportunity',
                'assignments.task'
            ])
            ->orderBy('accepted_at', 'desc')
            ->get()
            ->map(function($application) {
                return [
                    'id' => $application->id,
                    'volunteer_name' => $application->volunteer->name,
                    'volunteer_email' => $application->volunteer->email,
                    'volunteer_phone' => $application->volunteer->volunteerProfile->phone ?? 'N/A',
                    'volunteer_district' => $application->volunteer->volunteerProfile->district ?? 'N/A',
                    'volunteer_region' => $application->volunteer->volunteerProfile->region ?? 'N/A',
                    'volunteer_skills' => $application->volunteer->volunteerProfile->skills ?? [],
                    'opportunity_title' => $application->opportunity->title,
                    'opportunity_category' => $application->opportunity->category,
                    'accepted_at' => $application->accepted_at,
                    'application_message' => $application->message,
                    'relevant_experience' => $application->relevant_experience,
                    'total_assignments' => $application->assignments->count(),
                    'active_assignments' => $application->assignments->where('status', 'accepted')->count(),
                ];
            });
    }

    /**
     * Get monthly completed tasks for an organization
     */
    public function getMonthlyCompletedTasks($organizationId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return Assignment::whereHas('task.opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->with([
                'volunteer.volunteerProfile',
                'task.opportunity',
                'assignedBy'
            ])
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function($assignment) {
                $duration = null;
                if ($assignment->actual_start && $assignment->actual_end) {
                    $duration = Carbon::parse($assignment->actual_start)
                        ->diffInMinutes(Carbon::parse($assignment->actual_end));
                }

                return [
                    'id' => $assignment->id,
                    'task_title' => $assignment->task->title,
                    'task_description' => $assignment->task->description,
                    'task_priority' => $assignment->task->priority,
                    'opportunity_title' => $assignment->task->opportunity->title,
                    'volunteer_name' => $assignment->volunteer->name,
                    'volunteer_email' => $assignment->volunteer->email,
                    'volunteer_phone' => $assignment->volunteer->volunteerProfile->phone ?? 'N/A',
                    'assigned_by' => $assignment->assignedBy->name,
                    'assigned_at' => $assignment->assigned_at,
                    'accepted_at' => $assignment->accepted_at,
                    'completed_at' => $assignment->completed_at,
                    'scheduled_start' => $assignment->scheduled_start,
                    'scheduled_end' => $assignment->scheduled_end,
                    'actual_start' => $assignment->actual_start,
                    'actual_end' => $assignment->actual_end,
                    'duration_minutes' => $duration,
                    'performance_rating' => $assignment->performance_rating,
                    'performance_notes' => $assignment->performance_notes,
                    'volunteer_feedback' => $assignment->volunteer_feedback,
                    'task_completed_successfully' => $assignment->task_completed_successfully,
                    'checked_in_at' => $assignment->checked_in_at,
                    'checked_out_at' => $assignment->checked_out_at,
                ];
            });
    }

    /**
     * Get monthly failed tasks for an organization
     */
    public function getMonthlyFailedTasks($organizationId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Failed tasks include: cancelled, no_show, declined, or tasks marked as not completed successfully
        return Assignment::whereHas('task.opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    // Cancelled assignments
                    $q->where('status', 'cancelled')
                      ->whereBetween('updated_at', [$startDate, $endDate]);
                })->orWhere(function($q) use ($startDate, $endDate) {
                    // No-show assignments
                    $q->where('status', 'no_show')
                      ->whereBetween('updated_at', [$startDate, $endDate]);
                })->orWhere(function($q) use ($startDate, $endDate) {
                    // Declined assignments
                    $q->where('status', 'declined')
                      ->whereBetween('declined_at', [$startDate, $endDate]);
                })->orWhere(function($q) use ($startDate, $endDate) {
                    // Completed but marked as unsuccessful
                    $q->where('status', 'completed')
                      ->where('task_completed_successfully', false)
                      ->whereBetween('completed_at', [$startDate, $endDate]);
                });
            })
            ->with([
                'volunteer.volunteerProfile',
                'task.opportunity',
                'assignedBy'
            ])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($assignment) {
                $failureReason = match($assignment->status) {
                    'cancelled' => 'Task Cancelled',
                    'no_show' => 'Volunteer No-Show',
                    'declined' => 'Volunteer Declined',
                    'completed' => $assignment->task_completed_successfully === false ? 'Task Not Completed Successfully' : 'Unknown',
                    default => 'Unknown'
                };

                return [
                    'id' => $assignment->id,
                    'task_title' => $assignment->task->title,
                    'task_description' => $assignment->task->description,
                    'task_priority' => $assignment->task->priority,
                    'opportunity_title' => $assignment->task->opportunity->title,
                    'volunteer_name' => $assignment->volunteer->name,
                    'volunteer_email' => $assignment->volunteer->email,
                    'volunteer_phone' => $assignment->volunteer->volunteerProfile->phone ?? 'N/A',
                    'assigned_by' => $assignment->assignedBy->name,
                    'assigned_at' => $assignment->assigned_at,
                    'failure_date' => $assignment->declined_at ?? $assignment->updated_at,
                    'failure_reason' => $failureReason,
                    'decline_reason' => $assignment->decline_reason,
                    'assignment_notes' => $assignment->assignment_notes,
                    'volunteer_notes' => $assignment->volunteer_notes,
                    'scheduled_start' => $assignment->scheduled_start,
                    'scheduled_end' => $assignment->scheduled_end,
                    'status' => $assignment->status,
                    'performance_notes' => $assignment->performance_notes,
                ];
            });
    }

    /**
     * Get report statistics for summary
     */
    public function getReportStatistics($organizationId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Total opportunities in the month
        $totalOpportunities = DB::table('opportunities')
            ->where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Total applications in the month
        $totalApplications = Application::whereHas('opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereBetween('applied_at', [$startDate, $endDate])
            ->count();

        // Accepted applications (recruited volunteers)
        $recruitedVolunteers = Application::whereHas('opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'accepted')
            ->whereBetween('accepted_at', [$startDate, $endDate])
            ->count();

        // Total tasks created in the month
        $totalTasks = Task::whereHas('opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Completed tasks
        $completedTasks = Assignment::whereHas('task.opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->count();

        // Failed tasks
        $failedTasks = Assignment::whereHas('task.opportunity', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereIn('status', ['cancelled', 'no_show', 'declined'])
                      ->whereBetween('updated_at', [$startDate, $endDate]);
            })
            ->orWhere(function($query) use ($organizationId, $startDate, $endDate) {
                $query->whereHas('task.opportunity', function($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId);
                })
                ->where('status', 'completed')
                ->where('task_completed_successfully', false)
                ->whereBetween('completed_at', [$startDate, $endDate]);
            })
            ->count();

        return [
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'total_opportunities' => $totalOpportunities,
            'total_applications' => $totalApplications,
            'recruited_volunteers' => $recruitedVolunteers,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'failed_tasks' => $failedTasks,
            'success_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
            'failure_rate' => $totalTasks > 0 ? round(($failedTasks / $totalTasks) * 100, 2) : 0,
            'recruitment_rate' => $totalApplications > 0 ? round(($recruitedVolunteers / $totalApplications) * 100, 2) : 0,
        ];
    }
}
