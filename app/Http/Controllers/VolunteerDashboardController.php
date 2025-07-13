<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\Task;
use App\Models\Skill;
use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class VolunteerDashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        $user = $request->user();

        return response()->json([
            'dashboard' => 'volunteer',
            'user' => $user,
            'stats' => $this->getDashboardStats($user),
            'current_opportunities' => $this->getCurrentOpportunities($user),
            'active_tasks' => $this->getActiveTasks($user),
            'recent_activity' => $this->getRecentActivity($user),
            'upcoming_deadlines' => $this->getUpcomingDeadlines($user),
            'performance_metrics' => $this->getPerformanceMetrics($user),
            'achievements' => $this->getAchievements($user),
        ]);
    }

    /**
     * Get notifications for the authenticated volunteer
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = $request->user();

            // Get database notifications
            $dbNotifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data;

                    // Determine notification type and message based on data
                    $type = 'info';
                    $title = 'Notification';
                    $message = 'You have a new notification';

                    if (isset($data['status'])) {
                        switch ($data['status']) {
                            case 'accepted':
                                $type = 'success';
                                $title = 'Application Accepted';
                                $message = "Your application for \"{$data['opportunity_title']}\" has been accepted!";
                                break;
                            case 'rejected':
                                $type = 'warning';
                                $title = 'Application Rejected';
                                $message = "Your application for \"{$data['opportunity_title']}\" was not accepted.";
                                break;
                            case 'pending':
                                $type = 'info';
                                $title = 'Application Received';
                                $message = "Your application for \"{$data['opportunity_title']}\" is being reviewed.";
                                break;
                        }
                    }

                    return [
                        'id' => $notification->id,
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'timestamp' => $notification->created_at->toISOString(),
                        'read' => $notification->read_at !== null,
                        'data' => $data
                    ];
                });

            // Get recent application updates as notifications
            $recentApplications = Application::where('volunteer_id', $user->id)
                ->where('updated_at', '>', now()->subDays(30))
                ->with(['opportunity'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($application) {
                    $type = 'info';
                    $title = 'Application Update';
                    $message = "Update on your application for \"{$application->opportunity->title}\"";

                    switch ($application->status) {
                        case 'accepted':
                            $type = 'success';
                            $title = 'Application Accepted';
                            $message = "Your application for \"{$application->opportunity->title}\" has been accepted!";
                            break;
                        case 'rejected':
                            $type = 'warning';
                            $title = 'Application Rejected';
                            $message = "Your application for \"{$application->opportunity->title}\" was not accepted.";
                            break;
                        case 'pending':
                            $type = 'info';
                            $title = 'Application Submitted';
                            $message = "Your application for \"{$application->opportunity->title}\" is being reviewed.";
                            break;
                    }

                    return [
                        'id' => 'app_' . $application->id,
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'timestamp' => $application->updated_at->toISOString(),
                        'read' => true, // Mark application updates as read by default
                        'data' => [
                            'application_id' => $application->id,
                            'opportunity_id' => $application->opportunity_id,
                            'opportunity_title' => $application->opportunity->title,
                            'status' => $application->status
                        ]
                    ];
                });

            // Combine and sort notifications
            $allNotifications = $dbNotifications->concat($recentApplications)
                ->sortByDesc('timestamp')
                ->values()
                ->take(20);

            return response()->json([
                'data' => $allNotifications,
                'total' => $allNotifications->count(),
                'unread_count' => $dbNotifications->where('read', false)->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch notifications: ' . $e->getMessage(),
                'data' => [],
                'total' => 0,
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Get notification count for the authenticated volunteer (simplified endpoint)
     */
    public function getNotificationCount(Request $request)
    {
        try {
            $user = $request->user();

            // Get unread database notifications count
            $unreadDbNotifications = $user->notifications()
                ->whereNull('read_at')
                ->count();

            // Get recent application updates count (last 7 days)
            $recentApplicationUpdates = Application::where('volunteer_id', $user->id)
                ->where('updated_at', '>', now()->subDays(7))
                ->where('status', '!=', 'pending')
                ->count();

            $totalUnread = $unreadDbNotifications + $recentApplicationUpdates;

            return response()->json([
                'unread_count' => $totalUnread,
                'db_notifications' => $unreadDbNotifications,
                'recent_updates' => $recentApplicationUpdates,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'unread_count' => 0,
                'error' => 'Failed to fetch notification count: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();

            $notification = $user->notifications()->where('id', $id)->first();

            if ($notification) {
                $notification->markAsRead();
                return response()->json(['message' => 'Notification marked as read']);
            }

            return response()->json(['message' => 'Notification not found'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to mark notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time dashboard statistics for volunteer
     */
    private function getDashboardStats($user)
    {
        $userId = $user->id;

        // Application statistics
        $totalApplications = Application::where('volunteer_id', $userId)->count();
        $pendingApplications = Application::where('volunteer_id', $userId)
            ->where('status', 'pending')->count();
        $acceptedApplications = Application::where('volunteer_id', $userId)
            ->where('status', 'accepted')->count();
        $rejectedApplications = Application::where('volunteer_id', $userId)
            ->where('status', 'rejected')->count();

        // Task statistics
        $totalTasks = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })->count();

        $activeTasks = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })->where('status', 'in_progress')->count();

        $completedTasks = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })->where('status', 'completed')->count();

        $overdueTasks = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })->where('status', 'in_progress')
          ->where('end_date', '<', now())->count();

        // Opportunity statistics
        $currentOpportunities = Application::where('volunteer_id', $userId)
            ->where('status', 'accepted')
            ->whereHas('opportunity', function($query) {
                $query->where('status', 'active');
            })->count();

        $completedOpportunities = Application::where('volunteer_id', $userId)
            ->where('status', 'accepted')
            ->whereHas('opportunity', function($query) {
                $query->where('status', 'completed');
            })->count();

        // Calculate rates and averages
        $applicationSuccessRate = $totalApplications > 0
            ? round(($acceptedApplications / $totalApplications) * 100, 1)
            : 0;

        $taskCompletionRate = $totalTasks > 0
            ? round(($completedTasks / $totalTasks) * 100, 1)
            : 0;

        // Hours and impact
        $totalHoursVolunteered = $this->getTotalHoursVolunteered($userId);
        $thisMonthHours = $this->getThisMonthHours($userId);

        // Rating and feedback
        $averageRating = $this->getAverageRating($userId);
        $totalFeedbackReceived = $this->getTotalFeedbackReceived($userId);

        return [
            // Application metrics
            'total_applications' => $totalApplications,
            'pending_applications' => $pendingApplications,
            'accepted_applications' => $acceptedApplications,
            'rejected_applications' => $rejectedApplications,
            'application_success_rate' => $applicationSuccessRate,

            // Task metrics
            'total_tasks' => $totalTasks,
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
            'overdue_tasks' => $overdueTasks,
            'task_completion_rate' => $taskCompletionRate,

            // Opportunity metrics
            'current_opportunities' => $currentOpportunities,
            'completed_opportunities' => $completedOpportunities,

            // Performance metrics
            'total_hours_volunteered' => $totalHoursVolunteered,
            'this_month_hours' => $thisMonthHours,
            'average_rating' => $averageRating,
            'total_feedback_received' => $totalFeedbackReceived,

            // Engagement metrics
            'volunteer_since' => $user->created_at,
            'days_active' => now()->diffInDays($user->created_at),
            'organizations_worked_with' => $this->getOrganizationsWorkedWith($userId),
        ];
    }

    /**
     * Get current opportunities for volunteer
     */
    private function getCurrentOpportunities($user)
    {
        $userId = $user->id;

        return Application::where('volunteer_id', $userId)
            ->where('status', 'accepted')
            ->with(['opportunity.organization', 'tasks'])
            ->whereHas('opportunity', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($application) {
                $activeTasks = $application->tasks->where('status', 'in_progress')->count();
                $completedTasks = $application->tasks->where('status', 'completed')->count();
                $totalTasks = $application->tasks->count();

                $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

                return [
                    'id' => $application->id,
                    'opportunity' => $application->opportunity,
                    'organization' => $application->opportunity->organization,
                    'active_tasks' => $activeTasks,
                    'completed_tasks' => $completedTasks,
                    'total_tasks' => $totalTasks,
                    'progress' => $progress,
                    'joined_at' => $application->created_at,
                ];
            });
    }

    /**
     * Get active tasks for volunteer
     */
    private function getActiveTasks($user)
    {
        $userId = $user->id;

        return Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })
        ->where('status', 'in_progress')
        ->with(['opportunity.organization'])
        ->orderBy('end_date')
        ->limit(8)
        ->get()
        ->map(function($task) {
            $daysUntilDue = now()->diffInDays($task->end_date, false);
            $isOverdue = $daysUntilDue < 0;
            $isUrgent = $daysUntilDue <= 3 && $daysUntilDue >= 0;

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'start_date' => $task->start_date,
                'end_date' => $task->end_date,
                'days_until_due' => abs($daysUntilDue),
                'is_overdue' => $isOverdue,
                'is_urgent' => $isUrgent,
                'opportunity' => $task->opportunity,
                'organization' => $task->opportunity->organization,
                'progress' => rand(0, 100), // Placeholder for task progress
            ];
        });
    }

    /**
     * Get recent activity for volunteer
     */
    private function getRecentActivity($user)
    {
        $userId = $user->id;

        $recentApplications = Application::where('volunteer_id', $userId)
            ->with(['opportunity.organization'])
            ->where('created_at', '>=', now()->subDays(14))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($application) {
                return [
                    'id' => $application->id,
                    'type' => 'application',
                    'message' => 'Applied for ' . $application->opportunity->title,
                    'status' => $application->status,
                    'created_at' => $application->created_at,
                    'opportunity' => $application->opportunity,
                    'organization' => $application->opportunity->organization,
                ];
            });

        $recentTaskUpdates = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })
        ->where('updated_at', '>=', now()->subDays(14))
        ->with(['opportunity.organization'])
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function($task) {
            return [
                'id' => $task->id,
                'type' => 'task_update',
                'message' => 'Task "' . $task->title . '" status: ' . $task->status,
                'status' => $task->status,
                'created_at' => $task->updated_at,
                'task' => $task,
                'opportunity' => $task->opportunity,
                'organization' => $task->opportunity->organization,
            ];
        });

        return $recentApplications->concat($recentTaskUpdates)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();
    }

    /**
     * Helper methods
     */
    private function getTotalHoursVolunteered($userId)
    {
        $completedTasks = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })->where('status', 'completed')->count();

        return $completedTasks * 8; // Estimate 8 hours per task
    }

    private function getThisMonthHours($userId)
    {
        $completedTasksThisMonth = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })
        ->where('status', 'completed')
        ->whereYear('updated_at', now()->year)
        ->whereMonth('updated_at', now()->month)
        ->count();

        return $completedTasksThisMonth * 8;
    }

    private function getAverageRating($userId)
    {
        $avgRating = Feedback::whereHas('application', function($query) use ($userId) {
            $query->where('volunteer_id', $userId);
        })
        ->where('to_type', 'volunteer')
        ->avg('rating');

        return $avgRating ? round($avgRating, 1) : 0;
    }

    private function getTotalFeedbackReceived($userId)
    {
        return Feedback::whereHas('application', function($query) use ($userId) {
            $query->where('volunteer_id', $userId);
        })
        ->where('to_type', 'volunteer')
        ->count();
    }

    private function getOrganizationsWorkedWith($userId)
    {
        return Application::where('volunteer_id', $userId)
            ->where('status', 'accepted')
            ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
            ->distinct('opportunities.organization_id')
            ->count();
    }

    private function getUpcomingDeadlines($user)
    {
        $userId = $user->id;

        return Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })
        ->where('status', 'in_progress')
        ->where('end_date', '>=', now())
        ->where('end_date', '<=', now()->addDays(14))
        ->with(['opportunity.organization'])
        ->orderBy('end_date')
        ->limit(6)
        ->get()
        ->map(function($task) {
            $daysUntilDue = now()->diffInDays($task->end_date, false);

            return [
                'id' => $task->id,
                'title' => $task->title,
                'end_date' => $task->end_date,
                'days_until_due' => $daysUntilDue,
                'is_urgent' => $daysUntilDue <= 3,
                'opportunity' => $task->opportunity,
                'organization' => $task->opportunity->organization,
            ];
        });
    }

    private function getPerformanceMetrics($user)
    {
        $userId = $user->id;

        // Monthly activity for the last 6 months
        $months = [];
        $applications = [];
        $completedTasks = [];
        $hoursVolunteered = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            // Applications this month
            $monthlyApplications = Application::where('volunteer_id', $userId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $applications[] = $monthlyApplications;

            // Completed tasks this month
            $monthlyCompletedTasks = Task::whereHas('applications', function($query) use ($userId) {
                $query->where('volunteer_id', $userId)->where('status', 'accepted');
            })
            ->where('status', 'completed')
            ->whereYear('updated_at', $month->year)
            ->whereMonth('updated_at', $month->month)
            ->count();
            $completedTasks[] = $monthlyCompletedTasks;

            // Hours volunteered this month (estimated)
            $monthlyHours = $monthlyCompletedTasks * 8; // Estimate 8 hours per completed task
            $hoursVolunteered[] = $monthlyHours;
        }

        return [
            'months' => $months,
            'applications' => $applications,
            'completed_tasks' => $completedTasks,
            'hours_volunteered' => $hoursVolunteered,
        ];
    }

    private function getAchievements($user)
    {
        $userId = $user->id;
        $achievements = [];

        $completedTasks = Task::whereHas('applications', function($query) use ($userId) {
            $query->where('volunteer_id', $userId)->where('status', 'accepted');
        })->where('status', 'completed')->count();

        $totalHours = $this->getTotalHoursVolunteered($userId);
        $organizationsCount = $this->getOrganizationsWorkedWith($userId);

        // Define achievement thresholds
        $taskMilestones = [1, 5, 10, 25, 50];
        $hourMilestones = [10, 50, 100, 250, 500];
        $orgMilestones = [1, 3, 5];

        // Check task achievements
        foreach ($taskMilestones as $milestone) {
            if ($completedTasks >= $milestone) {
                $achievements[] = [
                    'type' => 'tasks',
                    'title' => "Task Completer",
                    'description' => "Completed {$milestone} tasks",
                    'achieved' => true,
                    'icon' => '✅',
                ];
            }
        }

        // Check hour achievements
        foreach ($hourMilestones as $milestone) {
            if ($totalHours >= $milestone) {
                $achievements[] = [
                    'type' => 'hours',
                    'title' => "Time Contributor",
                    'description' => "Volunteered {$milestone} hours",
                    'achieved' => true,
                    'icon' => '⏰',
                ];
            }
        }

        // Check organization achievements
        foreach ($orgMilestones as $milestone) {
            if ($organizationsCount >= $milestone) {
                $achievements[] = [
                    'type' => 'organizations',
                    'title' => "Organization Partner",
                    'description' => "Worked with {$milestone} organizations",
                    'achieved' => true,
                    'icon' => '🤝',
                ];
            }
        }

        return array_slice($achievements, -6); // Return last 6 achievements
    }
}
