<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\OrganizationProfile;
use App\Models\VolunteerProfile;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\Notification;

class AdminController extends Controller
{
    public function __construct()
    {
        // The middleware is already handled in routes, so we don't need it here
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        // Get comprehensive system statistics with real-time data
        $stats = [
            // User Statistics
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'pending_users' => User::where('account_status', 'pending_approval')->count(),
            'suspended_users' => User::where('account_status', 'suspended')->count(),

            // Volunteer Statistics with Profile Data
            'total_volunteers' => User::whereHas('roles', function($q) {
                $q->where('name', 'volunteer');
            })->count(),
            'active_volunteers' => User::whereHas('roles', function($q) {
                $q->where('name', 'volunteer');
            })->where('is_active', true)->count(),
            'volunteers_with_profiles' => VolunteerProfile::count(),
            'completed_volunteer_profiles' => VolunteerProfile::where('is_complete', true)->count(),
            'volunteers_available' => VolunteerProfile::where('availability_type', 'available')->count(),

            // Organization Statistics with Profile Data
            'total_organizations' => User::whereHas('roles', function($q) {
                $q->where('name', 'organization');
            })->count(),
            'pending_organizations' => OrganizationProfile::where('status', 'pending')->count(),
            'approved_organizations' => OrganizationProfile::where('status', 'approved')->count(),
            'rejected_organizations' => OrganizationProfile::where('status', 'rejected')->count(),
            'suspended_organizations' => OrganizationProfile::where('status', 'suspended')->count(),
            'verified_organizations' => OrganizationProfile::where('is_verified', true)->count(),

            // Opportunities & Applications
            'total_opportunities' => Opportunity::count(),
            'active_opportunities' => Opportunity::where('status', 'published')->count(),
            'draft_opportunities' => Opportunity::where('status', 'draft')->count(),
            'total_applications' => Application::count(),
            'pending_applications' => Application::where('status', 'pending')->count(),
            'accepted_applications' => Application::where('status', 'accepted')->count(),

            // Recent Activity Metrics
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_orgs_this_month' => OrganizationProfile::whereMonth('created_at', now()->month)->count(),
            'new_volunteers_this_month' => VolunteerProfile::whereMonth('created_at', now()->month)->count(),

            // Notifications
            'total_notifications' => Notification::count(),
            'unread_notifications' => Notification::where('status', 'unread')->count(),
        ];

        // Recent activities
        $recent_users = User::with('roles')->latest()->take(5)->get();
        $recent_organizations = OrganizationProfile::with('user')->latest()->take(5)->get();
        $pending_organizations = OrganizationProfile::where('status', 'pending')->with('user')->latest()->take(10)->get();

        // System health metrics
        $health = [
            'database_status' => $this->checkDatabaseHealth(),
            'storage_usage' => $this->getStorageUsage(),
            'recent_errors' => $this->getRecentErrors(),
        ];

        // Get volunteer profile insights
        $volunteer_insights = [
            'top_skills' => $this->getTopVolunteerSkills(),
            'districts_distribution' => $this->getVolunteerDistrictDistribution(),
            'education_levels' => $this->getVolunteerEducationLevels(),
            'availability_types' => $this->getVolunteerAvailabilityTypes(),
        ];

        // Get organization profile insights
        $organization_insights = [
            'organization_types' => $this->getOrganizationTypes(),
            'districts_distribution' => $this->getOrganizationDistrictDistribution(),
            'approval_timeline' => $this->getApprovalTimeline(),
            'registration_trend' => $this->getOrganizationRegistrationTrend(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recent_users',
            'recent_organizations',
            'pending_organizations',
            'health',
            'volunteer_insights',
            'organization_insights'
        ));
    }

    /**
     * System Settings
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * System Reports
     */
    public function reports()
    {
        // Generate various system reports
        $reports = [
            'user_registration_trend' => $this->getUserRegistrationTrend(),
            'organization_approval_stats' => $this->getOrganizationApprovalStats(),
            'volunteer_activity_stats' => $this->getVolunteerActivityStats(),
            'opportunity_stats' => $this->getOpportunityStats(),
        ];

        return view('admin.reports', compact('reports'));
    }

    /**
     * System Logs
     */
    public function logs()
    {
        // This would integrate with Laravel's logging system
        return view('admin.logs');
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Get storage usage
     */
    private function getStorageUsage()
    {
        $bytes = disk_free_space(storage_path());
        return $this->formatBytes($bytes);
    }

    /**
     * Get recent errors (placeholder)
     */
    private function getRecentErrors()
    {
        // This would integrate with error logging
        return 0;
    }

    /**
     * Get user registration trend
     */
    private function getUserRegistrationTrend()
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get organization approval stats
     */
    private function getOrganizationApprovalStats()
    {
        return [
            'pending' => OrganizationProfile::where('status', 'pending')->count(),
            'approved' => OrganizationProfile::where('status', 'approved')->count(),
            'rejected' => OrganizationProfile::where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get volunteer activity stats
     */
    private function getVolunteerActivityStats()
    {
        return [
            'total_volunteers' => User::whereHas('roles', function($q) {
                $q->where('name', 'volunteer');
            })->count(),
            'active_this_month' => User::whereHas('roles', function($q) {
                $q->where('name', 'volunteer');
            })->where('last_login_at', '>=', now()->subMonth())->count(),
        ];
    }

    /**
     * Get opportunity stats
     */
    private function getOpportunityStats()
    {
        return [
            'total' => Opportunity::count(),
            'published' => Opportunity::where('status', 'published')->count(),
            'draft' => Opportunity::where('status', 'draft')->count(),
            'closed' => Opportunity::where('status', 'closed')->count(),
        ];
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get top volunteer skills from database
     */
    private function getTopVolunteerSkills()
    {
        $skills = [];
        $volunteers = VolunteerProfile::whereNotNull('skills')->get();

        foreach ($volunteers as $volunteer) {
            if (is_array($volunteer->skills)) {
                foreach ($volunteer->skills as $skill) {
                    $skills[$skill] = ($skills[$skill] ?? 0) + 1;
                }
            }
        }

        arsort($skills);
        return array_slice($skills, 0, 10, true);
    }

    /**
     * Get volunteer district distribution
     */
    private function getVolunteerDistrictDistribution()
    {
        return VolunteerProfile::selectRaw('district, COUNT(*) as count')
            ->whereNotNull('district')
            ->groupBy('district')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->pluck('count', 'district')
            ->toArray();
    }

    /**
     * Get volunteer education levels
     */
    private function getVolunteerEducationLevels()
    {
        return VolunteerProfile::selectRaw('education_level, COUNT(*) as count')
            ->whereNotNull('education_level')
            ->groupBy('education_level')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'education_level')
            ->toArray();
    }

    /**
     * Get volunteer availability types
     */
    private function getVolunteerAvailabilityTypes()
    {
        return VolunteerProfile::selectRaw('availability_type, COUNT(*) as count')
            ->whereNotNull('availability_type')
            ->groupBy('availability_type')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'availability_type')
            ->toArray();
    }

    /**
     * Get organization types distribution
     */
    private function getOrganizationTypes()
    {
        return OrganizationProfile::selectRaw('org_type, COUNT(*) as count')
            ->whereNotNull('org_type')
            ->groupBy('org_type')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'org_type')
            ->toArray();
    }

    /**
     * Get organization district distribution
     */
    private function getOrganizationDistrictDistribution()
    {
        return OrganizationProfile::selectRaw('district, COUNT(*) as count')
            ->whereNotNull('district')
            ->groupBy('district')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->pluck('count', 'district')
            ->toArray();
    }

    /**
     * Get organization approval timeline (last 30 days)
     */
    private function getApprovalTimeline()
    {
        $timeline = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $timeline[$date] = [
                'approved' => OrganizationProfile::whereDate('updated_at', $date)->where('status', 'approved')->count(),
                'rejected' => OrganizationProfile::whereDate('updated_at', $date)->where('status', 'rejected')->count(),
                'pending' => OrganizationProfile::whereDate('created_at', $date)->where('status', 'pending')->count(),
            ];
        }
        return $timeline;
    }

    /**
     * Get organization registration trend (last 12 months)
     */
    private function getOrganizationRegistrationTrend()
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $trend[$monthKey] = OrganizationProfile::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }
        return $trend;
    }
}
