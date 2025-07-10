<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\TaskStatus;
use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrganizationDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        return response()->json([
            'dashboard' => 'organization',
            'user' => $request->user(),
            'stats' => [
                'opportunities_count' => $request->user()->opportunities()->count(),
                // add more as needed
            ],
        ]);
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
}
