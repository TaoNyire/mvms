<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Skill;
use App\Models\Feedback;
use App\Models\TaskStatus;
use App\Models\Application;
use App\Models\ApplicationTaskStatus;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        $user = $request->user();

        $data = [
            'dashboard' => 'organization',
            'user' => $user,
            'stats' => $this->getDashboardStats($user),
            'current_volunteers' => $this->getCurrentVolunteersForDashboard($user),
            'recent_activity' => $this->getRecentActivity($user),
            'upcoming_tasks' => $this->getUpcomingTasks($user),
            'monthly_trends' => $this->getMonthlyTrends($user),
            'performance_metrics' => $this->getPerformanceMetrics($user),
            'skills_overview' => $this->getSkillsOverview($user),
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Generate comprehensive reports for organization
     */
    public function reports(Request $request)
    {
        $user = $request->user();
        $organizationId = $user->id;

        // Date range filtering
        $startDate = $request->get('start_date', Carbon::now()->subMonths(12));
        $endDate = $request->get('end_date', Carbon::now());

        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }

        // Basic Statistics
        $totalOpportunities = Opportunity::where('organization_id', $organizationId)->count();
        $activeOpportunities = Opportunity::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();
        $totalApplications = Application::whereHas('opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->count();
        $totalVolunteers = Application::whereHas('opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->where('status', 'accepted')->distinct('volunteer_id')->count();

        // Application Status Breakdown
        $applicationsByStatus = Application::whereHas('opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get()
        ->pluck('count', 'status')
        ->toArray();

        // Task Status Breakdown
        $taskStatusBreakdown = TaskStatus::whereHas('application.opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get()
        ->pluck('count', 'status')
        ->toArray();

        // Monthly Applications Trend (last 12 months)
        $monthlyApplications = [];
        $monthlyVolunteers = [];
        $monthlyOpportunities = [];
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $monthlyApplications[] = Application::whereHas('opportunity', function($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();

            $monthlyVolunteers[] = Application::whereHas('opportunity', function($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->where('status', 'accepted')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->distinct('volunteer_id')
            ->count();

            $monthlyOpportunities[] = Opportunity::where('organization_id', $organizationId)
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();
        }

        // Top Performing Opportunities
        $topOpportunities = Opportunity::where('organization_id', $organizationId)
            ->withCount(['applications', 'applications as accepted_applications_count' => function($q) {
                $q->where('status', 'accepted');
            }])
            ->orderByDesc('applications_count')
            ->limit(10)
            ->get();

        // Recent Activity (last 30 days)
        $recentApplications = Application::whereHas('opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })
        ->with(['volunteer', 'opportunity'])
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        // Feedback Statistics
        $feedbackStats = Feedback::whereHas('application.opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })
        ->where('from_type', 'volunteer')
        ->selectRaw('
            COUNT(*) as total_feedback,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        ')
        ->first();

        // Volunteer Retention Rate
        $completedTasks = TaskStatus::whereHas('application.opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->where('status', 'completed')->count();

        $quitTasks = TaskStatus::whereHas('application.opportunity', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->where('status', 'quit')->count();

        $retentionRate = ($completedTasks + $quitTasks) > 0
            ? round(($completedTasks / ($completedTasks + $quitTasks)) * 100, 2)
            : 0;

        return response()->json([
            'summary' => [
                'total_opportunities' => $totalOpportunities,
                'active_opportunities' => $activeOpportunities,
                'total_applications' => $totalApplications,
                'total_volunteers' => $totalVolunteers,
                'retention_rate' => $retentionRate,
            ],
            'breakdowns' => [
                'applications_by_status' => $applicationsByStatus,
                'task_status_breakdown' => $taskStatusBreakdown,
            ],
            'trends' => [
                'months' => $months,
                'applications' => $monthlyApplications,
                'volunteers' => $monthlyVolunteers,
                'opportunities' => $monthlyOpportunities,
            ],
            'top_opportunities' => $topOpportunities,
            'recent_activity' => $recentApplications,
            'feedback_stats' => $feedbackStats,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get detailed information about current volunteers for organization
     */
    public function getCurrentVolunteersDetailed(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'data' => [],
                    'total' => 0
                ], 401);
            }

            $volunteers = Application::whereHas('opportunity', function($query) use ($user) {
                                         $query->where('organization_id', $user->id);
                                     })
                                     ->where('status', 'accepted')
                                     ->where('confirmation_status', 'confirmed')
                                     ->with([
                                         'volunteer.volunteerProfile',
                                         'task',
                                         'opportunity',
                                         'taskStatus'
                                     ])
                                     ->get()
                                     ->map(function($application) {
                                         $volunteer = $application->volunteer;
                                         $opportunity = $application->opportunity;
                                         $taskStatus = $application->taskStatus;
                                         $task = $application->task;

                                         // Get volunteer skills
                                         $volunteerSkills = $volunteer->skills ?? collect();

                                         return [
                                             'id' => $application->id,
                                             'volunteer_name' => $volunteer->name ?? 'Unknown',
                                             'volunteer_email' => $volunteer->email ?? 'No email',
                                             'opportunity_title' => $opportunity->title ?? 'Unknown opportunity',
                                             'applied_at' => $application->applied_at,
                                             'confirmed_at' => $application->confirmed_at,
                                             'task_status' => $taskStatus ? [
                                                 'status' => $taskStatus->status,
                                                 'started_at' => $taskStatus->started_at,
                                                 'completed_at' => $taskStatus->completed_at,
                                                 'completion_notes' => $taskStatus->completion_notes,
                                                 'work_evidence' => $taskStatus->work_evidence,
                                             ] : [
                                                 'status' => 'pending',
                                                 'started_at' => null,
                                                 'completed_at' => null,
                                                 'completion_notes' => null,
                                                 'work_evidence' => null,
                                             ],
                                             'task' => $task ? [
                                                 'id' => $task->id,
                                                 'title' => $task->title,
                                                 'description' => $task->description,
                                                 'start_date' => $task->start_date,
                                                 'end_date' => $task->end_date,
                                                 'status' => $task->status,
                                             ] : null,
                                             'volunteer' => [
                                                 'id' => $volunteer->id,
                                                 'name' => $volunteer->name,
                                                 'email' => $volunteer->email,
                                                 'phone' => $volunteer->phone ?? null,
                                                 'profile' => $volunteer->volunteerProfile ? [
                                                     'bio' => $volunteer->volunteerProfile->bio,
                                                     'experience' => $volunteer->volunteerProfile->experience,
                                                     'availability' => $volunteer->volunteerProfile->availability,
                                                 ] : null,
                                             ],
                                             'volunteer_skills' => $volunteerSkills->map(function($skill) {
                                                 return [
                                                     'id' => $skill->id,
                                                     'name' => $skill->name,
                                                     'category' => $skill->category ?? 'General',
                                                 ];
                                             })->toArray(),
                                             'opportunity' => [
                                                 'id' => $opportunity->id,
                                                 'title' => $opportunity->title,
                                                 'description' => $opportunity->description,
                                                 'location' => $opportunity->location,
                                                 'start_date' => $opportunity->start_date,
                                                 'end_date' => $opportunity->end_date,
                                                 'status' => $opportunity->status,
                                             ],
                                             'progress' => $this->calculateVolunteerProgress($taskStatus),
                                             'active_tasks' => $taskStatus && $taskStatus->status === 'in_progress' ? 1 : 0,
                                             'completed_tasks' => $taskStatus && $taskStatus->status === 'completed' ? 1 : 0,
                                             'joined_at' => $application->confirmed_at,
                                         ];
                                     });

            Log::info('getCurrentVolunteersDetailed called', [
                'user_id' => $user->id,
                'volunteers_count' => $volunteers->count(),
                'volunteers_data' => $volunteers->toArray()
            ]);

            return response()->json([
                'data' => $volunteers->toArray(),
                'total' => $volunteers->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getCurrentVolunteersDetailed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get recently employed volunteers (last 30 days by default)
     */
    public function getRecentlyEmployedVolunteers(Request $request)
    {
        try {
            $user = $request->user();
            $days = $request->get('days', 30);

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'data' => [],
                    'total' => 0
                ], 401);
            }

            $volunteers = Application::whereHas('opportunity', function($query) use ($user) {
                                         $query->where('organization_id', $user->id);
                                     })
                                     ->where('status', 'accepted')
                                     ->where('confirmation_status', 'confirmed')
                                     ->where('confirmed_at', '>=', now()->subDays($days))
                                     ->with([
                                         'volunteer.volunteerProfile',
                                         'task',
                                         'opportunity',
                                         'taskStatus'
                                     ])
                                     ->orderBy('confirmed_at', 'desc')
                                     ->get()
                                     ->map(function($application) {
                                         $volunteer = $application->volunteer;
                                         $opportunity = $application->opportunity;
                                         $taskStatus = $application->taskStatus;
                                         $task = $application->task;

                                         // Get volunteer skills
                                         $volunteerSkills = $volunteer->skills ?? collect();

                                         return [
                                             'id' => $application->id,
                                             'volunteer_name' => $volunteer->name ?? 'Unknown',
                                             'volunteer_email' => $volunteer->email ?? 'No email',
                                             'opportunity_title' => $opportunity->title ?? 'Unknown opportunity',
                                             'applied_at' => $application->applied_at,
                                             'confirmed_at' => $application->confirmed_at,
                                             'task_status' => $taskStatus ? [
                                                 'status' => $taskStatus->status,
                                                 'started_at' => $taskStatus->started_at,
                                                 'completed_at' => $taskStatus->completed_at,
                                                 'completion_notes' => $taskStatus->completion_notes,
                                                 'work_evidence' => $taskStatus->work_evidence,
                                             ] : [
                                                 'status' => 'pending',
                                                 'started_at' => null,
                                                 'completed_at' => null,
                                                 'completion_notes' => null,
                                                 'work_evidence' => null,
                                             ],
                                             'task' => $task ? [
                                                 'id' => $task->id,
                                                 'title' => $task->title,
                                                 'description' => $task->description,
                                                 'start_date' => $task->start_date,
                                                 'end_date' => $task->end_date,
                                                 'status' => $task->status,
                                             ] : null,
                                             'volunteer' => [
                                                 'id' => $volunteer->id,
                                                 'name' => $volunteer->name,
                                                 'email' => $volunteer->email,
                                                 'phone' => $volunteer->phone ?? null,
                                                 'profile' => $volunteer->volunteerProfile ? [
                                                     'bio' => $volunteer->volunteerProfile->bio,
                                                     'experience' => $volunteer->volunteerProfile->experience,
                                                     'availability' => $volunteer->volunteerProfile->availability,
                                                 ] : null,
                                             ],
                                             'volunteer_skills' => $volunteerSkills->map(function($skill) {
                                                 return [
                                                     'id' => $skill->id,
                                                     'name' => $skill->name,
                                                     'category' => $skill->category ?? 'General',
                                                 ];
                                             })->toArray(),
                                             'opportunity' => [
                                                 'id' => $opportunity->id,
                                                 'title' => $opportunity->title,
                                                 'description' => $opportunity->description,
                                                 'location' => $opportunity->location,
                                                 'start_date' => $opportunity->start_date,
                                                 'end_date' => $opportunity->end_date,
                                                 'status' => $opportunity->status,
                                             ],
                                             'progress' => $this->calculateVolunteerProgress($taskStatus),
                                             'active_tasks' => $taskStatus && $taskStatus->status === 'in_progress' ? 1 : 0,
                                             'completed_tasks' => $taskStatus && $taskStatus->status === 'completed' ? 1 : 0,
                                             'joined_at' => $application->confirmed_at,
                                         ];
                                     });

            return response()->json([
                'data' => $volunteers->toArray(),
                'total' => $volunteers->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getRecentlyEmployedVolunteers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for organization
     */
    private function getDashboardStats($user)
    {
        $organizationId = $user->id;

        // Basic statistics
        $totalOpportunities = Opportunity::where('organization_id', $organizationId)->count();
        $activeOpportunities = Opportunity::where('organization_id', $organizationId)
            ->where('status', 'active')->count();
        $completedOpportunities = Opportunity::where('organization_id', $organizationId)
            ->where('status', 'completed')->count();

        // Application statistics
        $totalApplications = Application::whereHas('opportunity', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->count();

        $pendingApplications = Application::whereHas('opportunity', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'pending')->count();

        $acceptedApplications = Application::whereHas('opportunity', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'accepted')->count();

        // Current volunteers
        $currentVolunteers = Application::whereHas('opportunity', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'accepted')
          ->where('confirmation_status', 'confirmed')->count();

        // New volunteers this month
        $newVolunteersThisMonth = Application::whereHas('opportunity', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'accepted')
          ->where('confirmation_status', 'confirmed')
          ->whereYear('confirmed_at', now()->year)
          ->whereMonth('confirmed_at', now()->month)->count();

        return [
            'total_opportunities' => $totalOpportunities,
            'active_opportunities' => $activeOpportunities,
            'completed_opportunities' => $completedOpportunities,
            'opportunity_completion_rate' => $totalOpportunities > 0 ? round(($completedOpportunities / $totalOpportunities) * 100, 1) : 0,

            'total_applications' => $totalApplications,
            'pending_applications' => $pendingApplications,
            'accepted_applications' => $acceptedApplications,
            'application_acceptance_rate' => $totalApplications > 0 ? round(($acceptedApplications / $totalApplications) * 100, 1) : 0,

            'current_volunteers' => $currentVolunteers,
            'new_volunteers_this_month' => $newVolunteersThisMonth,
            'volunteer_retention_rate' => 85, // Placeholder - would need more complex calculation

            'total_tasks' => 0, // Placeholder
            'active_tasks' => 0, // Placeholder
            'task_completion_rate' => 0, // Placeholder
        ];
    }

    /**
     * Get current volunteers for dashboard display
     */
    private function getCurrentVolunteersForDashboard($user)
    {
        return Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->where('status', 'accepted')
        ->where('confirmation_status', 'confirmed')
        ->with(['volunteer.volunteerProfile', 'opportunity', 'taskStatus', 'task'])
        ->limit(8)
        ->get()
        ->map(function($application) {
            $volunteer = $application->volunteer;
            $opportunity = $application->opportunity;
            $taskStatus = $application->taskStatus;
            $task = $application->task;

            return [
                'id' => $application->id,
                'volunteer' => [
                    'id' => $volunteer->id,
                    'name' => $volunteer->name,
                    'email' => $volunteer->email,
                    'phone' => $volunteer->phone ?? null,
                    'profile' => $volunteer->volunteerProfile ? [
                        'bio' => $volunteer->volunteerProfile->bio,
                        'experience' => $volunteer->volunteerProfile->experience,
                        'availability' => $volunteer->volunteerProfile->availability,
                    ] : null,
                ],
                'opportunity' => [
                    'id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'description' => $opportunity->description,
                    'location' => $opportunity->location,
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                    'status' => $opportunity->status,
                ],
                'task' => $task ? [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'status' => $task->status,
                ] : null,
                'task_status' => $taskStatus ? [
                    'status' => $taskStatus->status,
                    'started_at' => $taskStatus->started_at,
                    'completed_at' => $taskStatus->completed_at,
                    'completion_notes' => $taskStatus->completion_notes,
                    'work_evidence' => $taskStatus->work_evidence,
                ] : [
                    'status' => 'pending',
                    'started_at' => null,
                    'completed_at' => null,
                    'completion_notes' => null,
                    'work_evidence' => null,
                ],
                'progress' => $this->calculateVolunteerProgress($taskStatus),
                'active_tasks' => $taskStatus && $taskStatus->status === 'in_progress' ? 1 : 0,
                'completed_tasks' => $taskStatus && $taskStatus->status === 'completed' ? 1 : 0,
                'joined_at' => $application->confirmed_at,
            ];
        })->toArray();
    }

    /**
     * Get detailed task progress tracking for organization dashboard
     */
    public function getTaskProgressOverview(Request $request)
    {
        $user = $request->user();

        // Get all opportunities with their tasks and volunteer progress
        $opportunities = Opportunity::where('organization_id', $user->id)
            ->with([
                'tasks.applications.volunteer.volunteerProfile',
                'tasks.applications.taskStatus'
            ])
            ->get()
            ->map(function($opportunity) {
                $tasks = $opportunity->tasks->map(function($task) {
                    $volunteers = $task->applications()
                        ->where('status', 'accepted')
                        ->where('confirmation_status', 'confirmed')
                        ->with(['volunteer.volunteerProfile', 'taskStatus'])
                        ->get()
                        ->map(function($application) {
                            $taskStatus = $application->taskStatus;
                            return [
                                'application_id' => $application->id,
                                'volunteer' => [
                                    'id' => $application->volunteer->id,
                                    'name' => $application->volunteer->name,
                                    'email' => $application->volunteer->email,
                                    'profile' => $application->volunteer->volunteerProfile
                                ],
                                'task_status' => [
                                    'status' => $taskStatus->status ?? 'pending',
                                    'started_at' => $taskStatus->started_at ?? null,
                                    'completed_at' => $taskStatus->completed_at ?? null,
                                    'completion_notes' => $taskStatus->completion_notes ?? null,
                                    'duration_hours' => $taskStatus->duration_hours ?? null
                                ],
                                'progress_percentage' => $this->calculateProgressPercentage($taskStatus),
                                'applied_at' => $application->applied_at,
                                'responded_at' => $application->responded_at
                            ];
                        });

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'start_date' => $task->start_date,
                        'end_date' => $task->end_date,
                        'status' => $task->status,
                        'assigned_volunteers' => $task->assigned_volunteers,
                        'volunteers' => $volunteers,
                        'progress_summary' => [
                            'total' => $volunteers->count(),
                            'pending' => $volunteers->where('task_status.status', 'pending')->count(),
                            'in_progress' => $volunteers->where('task_status.status', 'in_progress')->count(),
                            'completed' => $volunteers->where('task_status.status', 'completed')->count(),
                            'quit' => $volunteers->where('task_status.status', 'quit')->count()
                        ]
                    ];
                });

                return [
                    'id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'description' => $opportunity->description,
                    'status' => $opportunity->status,
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                    'volunteers_needed' => $opportunity->volunteers_needed,
                    'tasks' => $tasks,
                    'overall_progress' => $this->calculateOpportunityProgress($tasks)
                ];
            });

        return response()->json([
            'opportunities' => $opportunities,
            'summary' => $this->getTaskProgressSummary($opportunities)
        ]);
    }

    /**
     * Calculate progress percentage for a task status
     */
    private function calculateProgressPercentage($taskStatus)
    {
        if (!$taskStatus) {
            return 0;
        }

        switch ($taskStatus->status) {
            case 'pending':
                return 0;
            case 'in_progress':
                return 50;
            case 'completed':
                return 100;
            case 'quit':
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Calculate overall progress for an opportunity
     */
    private function calculateOpportunityProgress($tasks)
    {
        $totalVolunteers = 0;
        $totalProgress = 0;

        foreach ($tasks as $task) {
            $volunteers = $task['volunteers'];
            $totalVolunteers += $volunteers->count();

            foreach ($volunteers as $volunteer) {
                $totalProgress += $volunteer['progress_percentage'];
            }
        }

        return $totalVolunteers > 0 ? round($totalProgress / $totalVolunteers, 2) : 0;
    }

    /**
     * Get task progress summary across all opportunities
     */
    private function getTaskProgressSummary($opportunities)
    {
        $summary = [
            'total_opportunities' => $opportunities->count(),
            'total_tasks' => 0,
            'total_volunteers' => 0,
            'volunteers_by_status' => [
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'quit' => 0
            ],
            'average_progress' => 0
        ];

        $totalProgress = 0;
        $totalVolunteers = 0;

        foreach ($opportunities as $opportunity) {
            foreach ($opportunity['tasks'] as $task) {
                $summary['total_tasks']++;
                $progressSummary = $task['progress_summary'];

                $summary['total_volunteers'] += $progressSummary['total'];
                $summary['volunteers_by_status']['pending'] += $progressSummary['pending'];
                $summary['volunteers_by_status']['in_progress'] += $progressSummary['in_progress'];
                $summary['volunteers_by_status']['completed'] += $progressSummary['completed'];
                $summary['volunteers_by_status']['quit'] += $progressSummary['quit'];

                $totalProgress += $opportunity['overall_progress'];
                $totalVolunteers++;
            }
        }

        $summary['average_progress'] = $totalVolunteers > 0 ? round($totalProgress / $totalVolunteers, 2) : 0;

        return $summary;
    }

    /**
     * Get recent activity for organization
     */
    private function getRecentActivity($user)
    {
        $activities = collect();

        // Recent applications
        $recentApplications = Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->with(['volunteer', 'opportunity'])
        ->where('created_at', '>=', now()->subDays(14))
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function($application) {
            return [
                'type' => 'application',
                'message' => $application->volunteer->name . ' applied for ' . $application->opportunity->title,
                'status' => $application->status,
                'created_at' => $application->created_at,
                'volunteer' => [
                    'id' => $application->volunteer->id,
                    'name' => $application->volunteer->name,
                    'email' => $application->volunteer->email,
                ],
                'opportunity' => [
                    'id' => $application->opportunity->id,
                    'title' => $application->opportunity->title,
                ],
            ];
        });

        // Recent task completions
        $recentTaskCompletions = ApplicationTaskStatus::whereHas('application.opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->where('status', 'completed')
        ->where('completed_at', '>=', now()->subDays(14))
        ->with(['application.volunteer', 'application.opportunity', 'application.task'])
        ->orderBy('completed_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function($taskStatus) {
            $application = $taskStatus->application;
            return [
                'type' => 'task_completion',
                'message' => $application->volunteer->name . ' completed task for ' . $application->opportunity->title,
                'status' => 'completed',
                'created_at' => $taskStatus->completed_at,
                'volunteer' => [
                    'id' => $application->volunteer->id,
                    'name' => $application->volunteer->name,
                    'email' => $application->volunteer->email,
                ],
                'opportunity' => [
                    'id' => $application->opportunity->id,
                    'title' => $application->opportunity->title,
                ],
                'task' => $application->task ? [
                    'id' => $application->task->id,
                    'title' => $application->task->title,
                ] : null,
            ];
        });

        // Combine and sort all activities
        $activities = $activities->merge($recentApplications)->merge($recentTaskCompletions);

        return $activities->sortByDesc('created_at')->take(10)->values()->toArray();
    }

    /**
     * Get upcoming tasks for organization
     */
    private function getUpcomingTasks($user)
    {
        return Task::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->where('status', 'active')
        ->where('start_date', '>', now())
        ->with(['opportunity', 'applications.volunteer'])
        ->orderBy('start_date', 'asc')
        ->limit(5)
        ->get()
        ->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'start_date' => $task->start_date,
                'end_date' => $task->end_date,
                'status' => $task->status,
                'opportunity' => [
                    'id' => $task->opportunity->id,
                    'title' => $task->opportunity->title,
                ],
                'assigned_volunteers' => $task->applications->where('status', 'accepted')->count(),
                'volunteers' => $task->applications->where('status', 'accepted')->map(function($application) {
                    return [
                        'id' => $application->volunteer->id,
                        'name' => $application->volunteer->name,
                        'email' => $application->volunteer->email,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function getMonthlyTrends($user)
    {
        $months = [];
        $applications = [];
        $volunteers = [];
        $completedTasks = [];

        // Get data for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');

            // Applications this month
            $monthlyApplications = Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();
            $applications[] = $monthlyApplications;

            // New volunteers this month
            $monthlyVolunteers = Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->whereYear('confirmed_at', $month->year)
            ->whereMonth('confirmed_at', $month->month)
            ->count();
            $volunteers[] = $monthlyVolunteers;

            // Completed tasks this month
            $monthlyCompletedTasks = ApplicationTaskStatus::whereHas('application.opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->where('status', 'completed')
            ->whereYear('completed_at', $month->year)
            ->whereMonth('completed_at', $month->month)
            ->count();
            $completedTasks[] = $monthlyCompletedTasks;
        }

        return [
            'months' => $months,
            'applications' => $applications,
            'volunteers' => $volunteers,
            'completed_tasks' => $completedTasks,
        ];
    }

    private function getPerformanceMetrics($user)
    {
        // Calculate average task completion time
        $completedTasks = ApplicationTaskStatus::whereHas('application.opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->where('status', 'completed')
        ->whereNotNull('started_at')
        ->whereNotNull('completed_at')
        ->get();

        $averageCompletionTime = 0;
        if ($completedTasks->count() > 0) {
            $totalDays = $completedTasks->sum(function($taskStatus) {
                return \Carbon\Carbon::parse($taskStatus->started_at)->diffInDays($taskStatus->completed_at);
            });
            $averageCompletionTime = round($totalDays / $completedTasks->count(), 1);
        }

        // Calculate task success rate
        $totalTasks = ApplicationTaskStatus::whereHas('application.opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })->count();

        $successfulTasks = ApplicationTaskStatus::whereHas('application.opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->where('status', 'completed')
        ->count();

        $taskSuccessRate = $totalTasks > 0 ? round(($successfulTasks / $totalTasks) * 100, 1) : 0;

        // Calculate volunteer satisfaction (placeholder - would need feedback system)
        $volunteerSatisfaction = 85; // Placeholder value

        return [
            'average_completion_time' => $averageCompletionTime,
            'volunteer_satisfaction' => $volunteerSatisfaction,
            'task_success_rate' => $taskSuccessRate,
            'total_completed_tasks' => $successfulTasks,
            'total_active_volunteers' => Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->count(),
        ];
    }

    private function getSkillsOverview($user)
    {
        // Get skills of all current volunteers
        $volunteerIds = Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->where('status', 'accepted')
        ->where('confirmation_status', 'confirmed')
        ->pluck('volunteer_id');

        $skills = Skill::whereHas('users', function($query) use ($volunteerIds) {
            $query->whereIn('users.id', $volunteerIds);
        })
        ->withCount(['users' => function($query) use ($volunteerIds) {
            $query->whereIn('users.id', $volunteerIds);
        }])
        ->orderBy('users_count', 'desc')
        ->limit(10)
        ->get()
        ->map(function($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category ?? 'General',
                'volunteer_count' => $skill->users_count,
            ];
        });

        return $skills->toArray();
    }

    /**
     * Test endpoint to debug volunteer data
     */
    public function testVolunteersData(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Get basic application data
        $applications = Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })->with(['volunteer', 'opportunity'])->get();

        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_roles' => $user->roles->pluck('name'),
            'has_organization_role' => $user->hasRole('organization'),
            'total_applications' => $applications->count(),
            'applications' => $applications->map(function($app) {
                return [
                    'id' => $app->id,
                    'status' => $app->status,
                    'confirmation_status' => $app->confirmation_status,
                    'volunteer_name' => $app->volunteer->name ?? 'Unknown',
                    'opportunity_title' => $app->opportunity->title ?? 'Unknown'
                ];
            }),
            'test_data' => [1, 2, 3] // Simple array to test
        ]);
    }

    /**
     * Debug endpoint to check user authentication and roles
     */
    public function debugAuth(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'authenticated' => $user ? true : false,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'has_organization_role' => $user->hasRole('organization'),
                'has_volunteer_role' => $user->hasRole('volunteer'),
                'has_admin_role' => $user->hasRole('admin'),
            ] : null,
            'headers' => $request->headers->all(),
            'bearer_token_present' => $request->bearerToken() ? true : false,
        ]);
    }

    /**
     * Get all current volunteers for organization panel
     */
    public function getOrganizationVolunteersList(Request $request)
    {
        try {
            $user = $request->user();
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', 'all'); // all, active, completed, pending

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'data' => [],
                    'total' => 0
                ], 401);
            }

            $query = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->with([
                'volunteer.volunteerProfile.skills',
                'task',
                'opportunity',
                'taskStatus'
            ]);

            // Apply search filter
            if (!empty($search)) {
                $query->whereHas('volunteer', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status !== 'all') {
                $query->whereHas('taskStatus', function($q) use ($status) {
                    $q->where('status', $status);
                });
            }

            $total = $query->count();
            $volunteers = $query->orderBy('confirmed_at', 'desc')
                               ->skip(($page - 1) * $perPage)
                               ->take($perPage)
                               ->get()
                               ->map(function($application) {
                                   $volunteer = $application->volunteer;
                                   $opportunity = $application->opportunity;
                                   $taskStatus = $application->taskStatus;
                                   $task = $application->task;

                                   return [
                                       'id' => $application->id,
                                       'volunteer_id' => $volunteer->id,
                                       'volunteer' => [
                                           'id' => $volunteer->id,
                                           'name' => $volunteer->name,
                                           'email' => $volunteer->email,
                                           'profile' => $volunteer->volunteerProfile ? [
                                               'bio' => $volunteer->volunteerProfile->bio,
                                               'location' => $volunteer->volunteerProfile->location,
                                               'district' => $volunteer->volunteerProfile->district,
                                               'region' => $volunteer->volunteerProfile->region,
                                               'availability' => $volunteer->volunteerProfile->availability,
                                           ] : null,
                                           'skills' => $volunteer->volunteerProfile && $volunteer->volunteerProfile->skills ?
                                               $volunteer->volunteerProfile->skills->map(function($skill) {
                                                   return [
                                                       'id' => $skill->id,
                                                       'name' => $skill->name,
                                                       'category' => $skill->category ?? 'General',
                                                   ];
                                               })->toArray() : [],
                                       ],
                                       'opportunity' => [
                                           'id' => $opportunity->id,
                                           'title' => $opportunity->title,
                                           'description' => $opportunity->description,
                                           'location' => $opportunity->location,
                                           'start_date' => $opportunity->start_date,
                                           'end_date' => $opportunity->end_date,
                                           'status' => $opportunity->status,
                                       ],
                                       'task' => $task ? [
                                           'id' => $task->id,
                                           'title' => $task->title,
                                           'description' => $task->description,
                                           'start_date' => $task->start_date,
                                           'end_date' => $task->end_date,
                                           'status' => $task->status,
                                       ] : null,
                                       'task_status' => $taskStatus ? [
                                           'status' => $taskStatus->status,
                                           'started_at' => $taskStatus->started_at,
                                           'completed_at' => $taskStatus->completed_at,
                                           'completion_notes' => $taskStatus->completion_notes,
                                           'work_evidence' => $taskStatus->work_evidence,
                                       ] : [
                                           'status' => 'pending',
                                           'started_at' => null,
                                           'completed_at' => null,
                                           'completion_notes' => null,
                                           'work_evidence' => null,
                                       ],
                                       'progress' => $this->calculateVolunteerProgress($taskStatus),
                                       'joined_at' => $application->confirmed_at,
                                       'application_date' => $application->applied_at,
                                   ];
                               });

            return response()->json([
                'success' => true,
                'data' => $volunteers,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching organization volunteers list: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch volunteers list',
                'message' => $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get detailed volunteer profile for organization view
     */
    public function getVolunteerProfile(Request $request, $volunteerId)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated'
                ], 401);
            }

            // Verify that this volunteer is associated with the organization
            $application = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->whereHas('volunteer', function($q) use ($volunteerId) {
                $q->where('id', $volunteerId);
            })
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->with([
                'volunteer.volunteerProfile.skills',
                'volunteer.applications.opportunity',
                'volunteer.applications.taskStatus',
                'opportunity',
                'task',
                'taskStatus'
            ])
            ->first();

            if (!$application) {
                return response()->json([
                    'error' => 'Volunteer not found or not associated with your organization'
                ], 404);
            }

            $volunteer = $application->volunteer;
            $volunteerProfile = $volunteer->volunteerProfile;

            // Get all applications for this volunteer with this organization
            $allApplications = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->where('volunteer_id', $volunteerId)
            ->with(['opportunity', 'task', 'taskStatus'])
            ->get();

            $volunteerData = [
                'id' => $volunteer->id,
                'name' => $volunteer->name,
                'email' => $volunteer->email,
                'status' => $volunteer->status,
                'created_at' => $volunteer->created_at,
                'profile' => $volunteerProfile ? [
                    'bio' => $volunteerProfile->bio,
                    'location' => $volunteerProfile->location,
                    'district' => $volunteerProfile->district,
                    'region' => $volunteerProfile->region,
                    'availability' => $volunteerProfile->availability,
                    'cv_url' => $volunteerProfile->cv_url,
                    'qualifications_url' => $volunteerProfile->qualifications_url,
                ] : null,
                'skills' => $volunteerProfile && $volunteerProfile->skills ?
                    $volunteerProfile->skills->map(function($skill) {
                        return [
                            'id' => $skill->id,
                            'name' => $skill->name,
                            'category' => $skill->category ?? 'General',
                            'description' => $skill->description ?? '',
                        ];
                    })->toArray() : [],
                'applications' => $allApplications->map(function($app) {
                    return [
                        'id' => $app->id,
                        'opportunity' => [
                            'id' => $app->opportunity->id,
                            'title' => $app->opportunity->title,
                            'description' => $app->opportunity->description,
                            'location' => $app->opportunity->location,
                            'start_date' => $app->opportunity->start_date,
                            'end_date' => $app->opportunity->end_date,
                            'status' => $app->opportunity->status,
                        ],
                        'task' => $app->task ? [
                            'id' => $app->task->id,
                            'title' => $app->task->title,
                            'description' => $app->task->description,
                            'start_date' => $app->task->start_date,
                            'end_date' => $app->task->end_date,
                            'status' => $app->task->status,
                        ] : null,
                        'task_status' => $app->taskStatus ? [
                            'status' => $app->taskStatus->status,
                            'started_at' => $app->taskStatus->started_at,
                            'completed_at' => $app->taskStatus->completed_at,
                            'completion_notes' => $app->taskStatus->completion_notes,
                            'work_evidence' => $app->taskStatus->work_evidence,
                        ] : null,
                        'applied_at' => $app->applied_at,
                        'confirmed_at' => $app->confirmed_at,
                        'status' => $app->status,
                        'confirmation_status' => $app->confirmation_status,
                        'progress' => $this->calculateVolunteerProgress($app->taskStatus),
                    ];
                })->toArray(),
                'statistics' => [
                    'total_applications' => $allApplications->count(),
                    'completed_tasks' => $allApplications->filter(function($app) {
                        return $app->taskStatus && $app->taskStatus->status === 'completed';
                    })->count(),
                    'active_tasks' => $allApplications->filter(function($app) {
                        return $app->taskStatus && $app->taskStatus->status === 'in_progress';
                    })->count(),
                    'pending_tasks' => $allApplications->filter(function($app) {
                        return !$app->taskStatus || $app->taskStatus->status === 'pending';
                    })->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $volunteerData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching volunteer profile: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch volunteer profile',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate volunteer progress based on task status
     */
    private function calculateVolunteerProgress($taskStatus)
    {
        if (!$taskStatus) {
            return 0;
        }

        switch ($taskStatus->status) {
            case 'completed':
                return 100;
            case 'in_progress':
                // Calculate based on time elapsed if dates are available
                if ($taskStatus->started_at && $taskStatus->application && $taskStatus->application->task) {
                    $task = $taskStatus->application->task;
                    if ($task->start_date && $task->end_date) {
                        $totalDuration = strtotime($task->end_date) - strtotime($task->start_date);
                        $elapsed = time() - strtotime($taskStatus->started_at);
                        $progress = min(90, max(10, ($elapsed / $totalDuration) * 100));
                        return round($progress);
                    }
                }
                return 50; // Default for in-progress
            case 'pending':
            default:
                return 0;
        }
    }
}
