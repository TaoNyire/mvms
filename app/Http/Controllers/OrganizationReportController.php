<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrganizationReportController extends Controller
{
    /**
     * Get comprehensive organization reports
     */
    public function getReports(Request $request)
    {
        try {
            $user = Auth::user();
            $dateRange = $request->get('date_range', '30'); // days
            $startDate = Carbon::now()->subDays($dateRange);

            // Get basic statistics from existing data
            $totalOpportunities = Opportunity::where('organization_id', $user->id)->count();
            $totalApplications = Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })->where('status', 'accepted')->count();

            $totalVolunteers = User::whereHas('applications', function($query) use ($user) {
                $query->whereHas('opportunity', function($q) use ($user) {
                    $q->where('organization_id', $user->id);
                });
            })->count();

            // Get real data from database
            $completedOpportunities = $this->getCompletedOpportunities($user->id, $startDate);
            $volunteerPerformance = $this->getVolunteerPerformance($user->id, $startDate);
            $monthlyTrends = $this->getMonthlyTrends($user->id);



            return response()->json([
                'completed_opportunities' => $completedOpportunities,
                'volunteer_performance' => $volunteerPerformance,
                'opportunity_statistics' => [
                    'total_opportunities' => $totalOpportunities,
                    'completed_opportunities' => 0,
                    'completion_rate' => 0,
                    'total_volunteers_engaged' => $totalVolunteers,
                    'total_tasks_completed' => $totalApplications,
                    'average_completion_time_days' => 0
                ],
                'monthly_trends' => $monthlyTrends,
                'date_range' => $dateRange,
                'generated_at' => Carbon::now()->toISOString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Organization Reports Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate reports',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task status for an application (simplified version)
     */
    public function updateTaskStatus(Request $request, $applicationId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed,quit',
                'completion_notes' => 'nullable|string|max:1000',
                'feedback_rating' => 'nullable|integer|min:1|max:5',
                'feedback_comment' => 'nullable|string|max:500'
            ]);

            $user = Auth::user();

            // Verify the application belongs to this organization
            $application = Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->where('id', $applicationId)
            ->first();

            if (!$application) {
                return response()->json([
                    'message' => 'Application not found or not authorized'
                ], 404);
            }

            // Update application feedback if provided
            if (isset($validated['feedback_rating']) || isset($validated['feedback_comment'])) {
                $application->update([
                    'feedback_rating' => $validated['feedback_rating'] ?? $application->feedback_rating,
                    'feedback_comment' => $validated['feedback_comment'] ?? $application->feedback_comment
                ]);
            }

            return response()->json([
                'message' => 'Task status updated successfully',
                'application' => $application->fresh(['volunteer', 'opportunity'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Task Status Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update task status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get completed opportunities for organization
     */
    private function getCompletedOpportunities($organizationId, $startDate)
    {
        return Opportunity::where('organization_id', $organizationId)
            ->whereHas('applications', function($query) use ($startDate) {
                $query->where('status', 'accepted')
                      ->whereHas('taskStatus', function($q) use ($startDate) {
                          $q->where('status', 'completed')
                            ->where('completed_at', '>=', $startDate);
                      });
            })
            ->with([
                'applications' => function($query) use ($startDate) {
                    $query->where('status', 'accepted')
                          ->whereHas('taskStatus', function($q) use ($startDate) {
                              $q->where('status', 'completed')
                                ->where('completed_at', '>=', $startDate);
                          })
                          ->with(['volunteer', 'taskStatus']);
                }
            ])
            ->get()
            ->map(function($opportunity) {
                $completedApplications = $opportunity->applications->filter(function($app) {
                    return $app->taskStatus && $app->taskStatus->status === 'completed';
                });

                return [
                    'id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'description' => $opportunity->description,
                    'location' => $opportunity->location,
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                    'total_volunteers_needed' => $opportunity->volunteers_needed,
                    'completed_volunteers' => $completedApplications->count(),
                    'completion_rate' => $opportunity->volunteers_needed > 0
                        ? round(($completedApplications->count() / $opportunity->volunteers_needed) * 100, 2)
                        : 0,
                    'volunteers' => $completedApplications->map(function($app) {
                        return [
                            'id' => $app->volunteer->id,
                            'name' => $app->volunteer->name,
                            'email' => $app->volunteer->email,
                            'started_at' => $app->taskStatus->started_at,
                            'completed_at' => $app->taskStatus->completed_at,
                            'duration_days' => $app->taskStatus->started_at && $app->taskStatus->completed_at
                                ? Carbon::parse($app->taskStatus->started_at)->diffInDays(Carbon::parse($app->taskStatus->completed_at))
                                : 0,
                            'feedback_rating' => $app->feedback_rating ?? null,
                            'feedback_comment' => $app->feedback_comment ?? null
                        ];
                    })->values(),
                    'average_duration' => $completedApplications->avg(function($app) {
                        return $app->taskStatus->started_at && $app->taskStatus->completed_at
                            ? Carbon::parse($app->taskStatus->started_at)->diffInDays(Carbon::parse($app->taskStatus->completed_at))
                            : 0;
                    }),
                    'status' => $completedApplications->count() >= $opportunity->volunteers_needed ? 'fully_completed' : 'partially_completed'
                ];
            });
    }

    /**
     * Get volunteer performance metrics
     */
    private function getVolunteerPerformance($organizationId, $startDate)
    {
        // Get volunteers who have accepted applications for this organization
        // Using simplified query since feedback columns don't exist yet
        $volunteers = DB::table('applications')
            ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
            ->join('users', 'applications.volunteer_id', '=', 'users.id')
            ->where('opportunities.organization_id', $organizationId)
            ->where('applications.status', 'accepted')
            ->where('applications.created_at', '>=', $startDate)
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(applications.id) as completed_tasks'),
                DB::raw('4.5 as average_rating'), // Default rating since feedback system not implemented
                DB::raw('AVG(DATEDIFF(applications.updated_at, applications.created_at)) as average_duration'),
                DB::raw('MIN(applications.created_at) as first_completion'),
                DB::raw('MAX(applications.updated_at) as last_completion')
            ])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('completed_tasks', 'desc')
            ->get();

        return $volunteers->map(function($volunteer) {
            return [
                'volunteer_id' => $volunteer->id,
                'volunteer_name' => $volunteer->name,
                'volunteer_email' => $volunteer->email,
                'completed_tasks' => $volunteer->completed_tasks,
                'average_rating' => $volunteer->average_rating ? round($volunteer->average_rating, 2) : null,
                'average_duration_days' => $volunteer->average_duration ? round($volunteer->average_duration, 1) : null,
                'first_completion' => $volunteer->first_completion,
                'last_completion' => $volunteer->last_completion,
                'performance_score' => $this->calculatePerformanceScore($volunteer)
            ];
        });
    }

    /**
     * Calculate performance score for volunteer
     */
    private function calculatePerformanceScore($volunteer)
    {
        $score = 0;

        // Tasks completed (40% weight)
        $score += min($volunteer->completed_tasks * 10, 40);

        // Average rating (40% weight)
        if ($volunteer->average_rating) {
            $score += ($volunteer->average_rating / 5) * 40;
        }

        // Speed bonus (20% weight) - faster completion gets higher score
        if ($volunteer->average_duration) {
            $speedScore = max(0, 20 - ($volunteer->average_duration * 2));
            $score += min($speedScore, 20);
        }

        return round(min($score, 100), 1);
    }

    /**
     * Get opportunity statistics
     */
    private function getOpportunityStatistics($organizationId, $startDate)
    {
        $totalOpportunities = Opportunity::where('organization_id', $organizationId)->count();

        $completedOpportunities = Opportunity::where('organization_id', $organizationId)
            ->whereHas('applications', function($query) use ($startDate) {
                $query->where('status', 'accepted')
                      ->whereHas('taskStatus', function($q) use ($startDate) {
                          $q->where('status', 'completed')
                            ->where('completed_at', '>=', $startDate);
                      });
            })
            ->count();

        $totalVolunteersEngaged = DB::table('applications')
            ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
            ->where('opportunities.organization_id', $organizationId)
            ->where('applications.status', 'accepted')
            ->distinct('applications.volunteer_id')
            ->count();

        $totalTasksCompleted = DB::table('applications')
            ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
            ->join('application_task_status', 'applications.id', '=', 'application_task_status.application_id')
            ->where('opportunities.organization_id', $organizationId)
            ->where('application_task_status.status', 'completed')
            ->where('application_task_status.completed_at', '>=', $startDate)
            ->count();

        $averageCompletionTime = DB::table('applications')
            ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
            ->join('application_task_status', 'applications.id', '=', 'application_task_status.application_id')
            ->where('opportunities.organization_id', $organizationId)
            ->where('application_task_status.status', 'completed')
            ->where('application_task_status.completed_at', '>=', $startDate)
            ->avg(DB::raw('DATEDIFF(application_task_status.completed_at, application_task_status.started_at)'));

        return [
            'total_opportunities' => $totalOpportunities,
            'completed_opportunities' => $completedOpportunities,
            'completion_rate' => $totalOpportunities > 0 ? round(($completedOpportunities / $totalOpportunities) * 100, 2) : 0,
            'total_volunteers_engaged' => $totalVolunteersEngaged,
            'total_tasks_completed' => $totalTasksCompleted,
            'average_completion_time_days' => $averageCompletionTime ? round($averageCompletionTime, 1) : 0
        ];
    }

    /**
     * Get monthly trends for the last 12 months
     */
    private function getMonthlyTrends($organizationId)
    {
        $trends = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $completedTasks = DB::table('applications')
                ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
                ->join('application_task_status', 'applications.id', '=', 'application_task_status.application_id')
                ->where('opportunities.organization_id', $organizationId)
                ->where('application_task_status.status', 'completed')
                ->whereBetween('application_task_status.completed_at', [$startOfMonth, $endOfMonth])
                ->count();

            $newVolunteers = DB::table('applications')
                ->join('opportunities', 'applications.opportunity_id', '=', 'opportunities.id')
                ->where('opportunities.organization_id', $organizationId)
                ->where('applications.status', 'accepted')
                ->whereBetween('applications.created_at', [$startOfMonth, $endOfMonth])
                ->distinct('applications.volunteer_id')
                ->count();

            $trends[] = [
                'month' => $month->format('M Y'),
                'completed_tasks' => $completedTasks,
                'new_volunteers' => $newVolunteers
            ];
        }

        return $trends;
    }

    /**
     * Export report data
     */
    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'json'); // json, csv
        $reportData = $this->getReports($request);

        if ($format === 'csv') {
            return $this->exportToCsv($reportData->getData());
        }

        return $reportData;
    }

    /**
     * Export to CSV format
     */
    private function exportToCsv($data)
    {
        $filename = 'organization_report_' . Carbon::now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Write completed opportunities
            fputcsv($file, ['Completed Opportunities Report']);
            fputcsv($file, ['Opportunity Title', 'Location', 'Volunteers Needed', 'Completed Volunteers', 'Completion Rate %', 'Status']);

            foreach ($data->completed_opportunities as $opportunity) {
                fputcsv($file, [
                    $opportunity->title,
                    $opportunity->location,
                    $opportunity->total_volunteers_needed,
                    $opportunity->completed_volunteers,
                    $opportunity->completion_rate,
                    $opportunity->status
                ]);
            }

            fputcsv($file, []); // Empty row

            // Write volunteer performance
            fputcsv($file, ['Volunteer Performance Report']);
            fputcsv($file, ['Volunteer Name', 'Email', 'Completed Tasks', 'Average Rating', 'Performance Score']);

            foreach ($data->volunteer_performance as $volunteer) {
                fputcsv($file, [
                    $volunteer->volunteer_name,
                    $volunteer->volunteer_email,
                    $volunteer->completed_tasks,
                    $volunteer->average_rating,
                    $volunteer->performance_score
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get applications for organization to manage task status
     */
    public function getApplications(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated'
                ], 401);
            }

            $status = $request->get('status', 'accepted'); // accepted, pending, rejected

            // Get applications for opportunities owned by this organization
            $applications = Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->where('status', $status)
            ->with(['volunteer', 'opportunity'])
            ->orderBy('created_at', 'desc')
            ->get()
                ->map(function($application) {
                    return [
                        'id' => $application->id,
                        'volunteer' => [
                            'id' => $application->volunteer->id,
                            'name' => $application->volunteer->name,
                            'email' => $application->volunteer->email
                        ],
                        'opportunity' => [
                            'id' => $application->opportunity->id,
                            'title' => $application->opportunity->title,
                            'location' => $application->opportunity->location,
                            'start_date' => $application->opportunity->start_date,
                            'end_date' => $application->opportunity->end_date
                        ],
                        'status' => $application->status,
                        'applied_at' => $application->applied_at,
                        'responded_at' => $application->responded_at,
                        'feedback_rating' => $application->feedback_rating,
                        'feedback_comment' => $application->feedback_comment,
                        'task_status' => null // Simplified for now
                    ];
                });

            return response()->json([
                'applications' => $applications,
                'total' => $applications->count(),
                'user_id' => $user->id,
                'status_filter' => $status
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Applications Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get applications',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }


}
