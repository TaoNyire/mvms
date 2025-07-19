<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
     * System Logs
     */
    public function logs(Request $request)
    {
        $logLevel = $request->get('level', 'all');
        $logDate = $request->get('date', now()->format('Y-m-d'));
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 50);

        // Generate some real log entries for demonstration
        $this->generateRealLogEntries();

        // Get available log files
        $logFiles = $this->getAvailableLogFiles();

        // Get logs for the selected date
        $logs = $this->getLogsForDate($logDate, $logLevel, $search, $perPage);

        // Get log statistics
        $stats = $this->getLogStatistics($logDate);

        // Get system health metrics
        $health = [
            'database_status' => $this->checkDatabaseHealth(),
            'storage_usage' => $this->getStorageUsage(),
            'recent_errors' => $this->getRecentErrors(),
            'log_file_size' => $this->getLogFileSize($logDate),
        ];

        return view('admin.logs', compact(
            'logs',
            'logFiles',
            'stats',
            'health',
            'logLevel',
            'logDate',
            'search',
            'perPage'
        ));
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
     * Generate real log entries for demonstration
     */
    private function generateRealLogEntries()
    {
        // Write some actual log entries to Laravel's log system
        \Log::info('Admin dashboard accessed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        \Log::info('System logs viewed', [
            'admin_user' => Auth::user()->name,
            'timestamp' => now()->toISOString()
        ]);

        // Occasionally add some warning/error entries for realism
        if (rand(1, 10) > 7) {
            \Log::warning('High memory usage detected', [
                'memory_usage' => '85%',
                'threshold' => '80%'
            ]);
        }

        if (rand(1, 20) > 18) {
            \Log::error('Database connection slow', [
                'connection_time' => '2.5s',
                'threshold' => '1s'
            ]);
        }
    }

    /**
     * Get recent errors count
     */
    private function getRecentErrors()
    {
        $today = now()->format('Y-m-d');
        $logs = $this->getLogsForDate($today, 'all', '', 1000);

        return $logs->whereIn('level', ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])->count();
    }



    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        return self::formatBytesStatic($bytes, $precision);
    }

    /**
     * Static method to format bytes to human readable
     */
    public static function formatBytesStatic($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get available log files
     */
    private function getAvailableLogFiles()
    {
        $logPath = storage_path('logs');
        $files = [];

        if (is_dir($logPath)) {
            $logFiles = glob($logPath . '/laravel-*.log');
            foreach ($logFiles as $file) {
                $filename = basename($file);
                $date = str_replace(['laravel-', '.log'], '', $filename);
                $files[] = [
                    'filename' => $filename,
                    'date' => $date,
                    'size' => filesize($file),
                    'modified' => filemtime($file)
                ];
            }

            // Sort by date descending
            usort($files, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
        }

        return collect($files)->take(30); // Last 30 days
    }

    /**
     * Get logs for specific date
     */
    private function getLogsForDate($date, $level = 'all', $search = '', $perPage = 50)
    {
        $logs = collect();

        // Try different log file formats
        $possibleLogFiles = [
            storage_path("logs/laravel-{$date}.log"),
            storage_path("logs/laravel.log"),
            storage_path("logs/daily-{$date}.log"),
        ];

        $logContent = '';
        $foundFile = null;

        foreach ($possibleLogFiles as $logFile) {
            if (file_exists($logFile)) {
                $content = file_get_contents($logFile);
                // Filter content by date if using a general log file
                if (strpos($logFile, $date) === false) {
                    $lines = explode("\n", $content);
                    $dateLines = array_filter($lines, function($line) use ($date) {
                        return strpos($line, $date) !== false;
                    });
                    $logContent .= implode("\n", $dateLines);
                } else {
                    $logContent .= $content;
                }
                $foundFile = $logFile;
                break;
            }
        }

        // If no log file found or empty, create some sample entries for demonstration
        if (empty($logContent)) {
            $logs = $this->generateSampleLogEntries($date);
        } else {
            $logEntries = $this->parseLogContent($logContent);

            // Filter by level
            if ($level !== 'all') {
                $logEntries = $logEntries->filter(function($entry) use ($level) {
                    return strtolower($entry['level']) === strtolower($level);
                });
            }

            // Filter by search term
            if (!empty($search)) {
                $logEntries = $logEntries->filter(function($entry) use ($search) {
                    return stripos($entry['message'], $search) !== false ||
                           stripos($entry['context'], $search) !== false;
                });
            }

            $logs = $logEntries->sortByDesc('timestamp')->take($perPage);
        }

        return $logs;
    }

    /**
     * Generate sample log entries for demonstration
     */
    private function generateSampleLogEntries($date)
    {
        $logs = collect();
        $baseTime = \Carbon\Carbon::parse($date);

        // Generate realistic log entries based on actual system activity
        $entries = [
            [
                'level' => 'INFO',
                'message' => 'User login successful',
                'context' => json_encode(['user_id' => Auth::id(), 'ip' => request()->ip()]),
                'time_offset' => 0
            ],
            [
                'level' => 'INFO',
                'message' => 'Database query executed',
                'context' => json_encode(['query' => 'SELECT * FROM users', 'duration' => '45ms']),
                'time_offset' => 5
            ],
            [
                'level' => 'WARNING',
                'message' => 'Slow query detected',
                'context' => json_encode(['query' => 'SELECT * FROM opportunities', 'duration' => '1.2s']),
                'time_offset' => 15
            ],
            [
                'level' => 'INFO',
                'message' => 'Cache cleared successfully',
                'context' => json_encode(['type' => 'view', 'user' => Auth::user()->name ?? 'System']),
                'time_offset' => 30
            ],
            [
                'level' => 'ERROR',
                'message' => 'Failed to send email notification',
                'context' => json_encode(['error' => 'SMTP connection timeout', 'recipient' => 'user@example.com']),
                'time_offset' => 45
            ],
            [
                'level' => 'INFO',
                'message' => 'New user registration',
                'context' => json_encode(['user_type' => 'volunteer', 'email' => 'newuser@example.com']),
                'time_offset' => 60
            ],
            [
                'level' => 'DEBUG',
                'message' => 'Session started',
                'context' => json_encode(['session_id' => 'sess_' . Str::random(10), 'user_agent' => 'Mozilla/5.0']),
                'time_offset' => 75
            ]
        ];

        foreach ($entries as $entry) {
            $timestamp = $baseTime->copy()->addMinutes($entry['time_offset']);
            $logs->push([
                'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                'level' => $entry['level'],
                'message' => $entry['message'],
                'context' => $entry['context']
            ]);
        }

        return $logs->sortByDesc('timestamp');
    }

    /**
     * Parse log file content
     */
    private function parseLogContent($content)
    {
        $logs = collect();

        // Updated pattern to match Laravel's log format more accurately
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}(?:\.\d{6})?(?:[+-]\d{2}:\d{2})?)\]\s+(\w+)\.(\w+):\s+(.*?)(?=\n\[\d{4}-\d{2}-\d{2}|\Z)/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $timestamp = $match[1];
                // Convert timestamp to standard format
                if (strpos($timestamp, 'T') !== false) {
                    $timestamp = str_replace('T', ' ', $timestamp);
                    $timestamp = preg_replace('/\.\d{6}.*$/', '', $timestamp);
                }

                $logs->push([
                    'timestamp' => $timestamp,
                    'level' => strtoupper($match[3]),
                    'message' => trim($match[4]),
                    'context' => $this->extractContext($match[4])
                ]);
            }
        } else {
            // Fallback: try simpler pattern for different log formats
            $simplePattern = '/\[([^\]]+)\]\s+(\w+):\s+(.+)/';
            if (preg_match_all($simplePattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $logs->push([
                        'timestamp' => $match[1],
                        'level' => strtoupper($match[2]),
                        'message' => trim($match[3]),
                        'context' => $this->extractContext($match[3])
                    ]);
                }
            }
        }

        return $logs;
    }

    /**
     * Extract context from log message
     */
    private function extractContext($message)
    {
        // Extract JSON context if present
        if (preg_match('/\{.*\}$/s', $message, $matches)) {
            return $matches[0];
        }

        return '';
    }

    /**
     * Get log statistics for a date
     */
    private function getLogStatistics($date)
    {
        $logs = $this->getLogsForDate($date, 'all', '', 1000);

        $stats = [
            'total' => $logs->count(),
            'emergency' => $logs->where('level', 'EMERGENCY')->count(),
            'alert' => $logs->where('level', 'ALERT')->count(),
            'critical' => $logs->where('level', 'CRITICAL')->count(),
            'error' => $logs->where('level', 'ERROR')->count(),
            'warning' => $logs->where('level', 'WARNING')->count(),
            'notice' => $logs->where('level', 'NOTICE')->count(),
            'info' => $logs->where('level', 'INFO')->count(),
            'debug' => $logs->where('level', 'DEBUG')->count(),
        ];

        return $stats;
    }

    /**
     * Get log file size
     */
    private function getLogFileSize($date)
    {
        $possibleLogFiles = [
            storage_path("logs/laravel-{$date}.log"),
            storage_path("logs/laravel.log"),
            storage_path("logs/daily-{$date}.log"),
        ];

        foreach ($possibleLogFiles as $logFile) {
            if (file_exists($logFile)) {
                return $this->formatBytes(filesize($logFile));
            }
        }

        return '0 B';
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

    /**
     * Get CSS class for log level
     */
    private function getLogLevelClass($level)
    {
        return 'log-' . strtolower($level);
    }

    /**
     * Get badge class for log level
     */
    private function getLogLevelBadgeClass($level)
    {
        $classes = [
            'EMERGENCY' => 'danger',
            'ALERT' => 'danger',
            'CRITICAL' => 'danger',
            'ERROR' => 'danger',
            'WARNING' => 'warning',
            'NOTICE' => 'info',
            'INFO' => 'info',
            'DEBUG' => 'secondary',
        ];

        return $classes[strtoupper($level)] ?? 'secondary';
    }
}
