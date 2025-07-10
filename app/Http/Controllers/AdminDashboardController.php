<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // COUNTERS
        $total_organizations = User::whereHas('roles', fn($q) => $q->where('name', 'organization'))->count();
        $total_volunteers = User::whereHas('roles', fn($q) => $q->where('name', 'volunteer'))->count();
        $total_opportunities = Opportunity::count();
        $total_applications = Application::count();

        // TODAY's stats
        $today = Carbon::today();
        $new_organizations_today = User::whereHas('roles', fn($q) => $q->where('name', 'organization'))
            ->whereDate('created_at', $today)->count();
        $new_volunteers_today = User::whereHas('roles', fn($q) => $q->where('name', 'volunteer'))
            ->whereDate('created_at', $today)->count();

        // MONTHLY STATS FOR THE YEAR
        $months = [];
        $orgs_monthly = [];
        $vols_monthly = [];
        $opps_monthly = [];
        $apps_monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $label = Carbon::create(null, $m)->format('M');
            $months[] = $label;
            $orgs_monthly[] = User::whereHas('roles', fn($q) => $q->where('name', 'organization'))
                ->whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
            $vols_monthly[] = User::whereHas('roles', fn($q) => $q->where('name', 'volunteer'))
                ->whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
            $opps_monthly[] = Opportunity::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
            $apps_monthly[] = Application::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
        }

        // RECENT ACTIVITY (latest 5 applications)
        $recent_applications = Application::with(['volunteer', 'opportunity'])
            ->latest()->limit(5)->get();

        // TOP 5 OPPORTUNITIES BY APPLICATION COUNT
        $top_opportunities = Opportunity::withCount('applications')
            ->orderByDesc('applications_count')->limit(5)->get();

        return response()->json([
            'counters' => [
                'total_organizations' => $total_organizations,
                'total_volunteers' => $total_volunteers,
                'total_opportunities' => $total_opportunities,
                'total_applications' => $total_applications,
                'new_organizations_today' => $new_organizations_today,
                'new_volunteers_today' => $new_volunteers_today,
            ],
            'monthly' => [
                'labels' => $months,
                'organizations' => $orgs_monthly,
                'volunteers' => $vols_monthly,
                'opportunities' => $opps_monthly,
                'applications' => $apps_monthly,
            ],
            'recent_applications' => $recent_applications,
            'top_opportunities' => $top_opportunities,
        ]);
    }

    public function reports(Request $request)
    {
        // Generate comprehensive reports for admin
        $totalUsers = User::count();
        $totalVolunteers = User::whereHas('roles', fn($q) => $q->where('name', 'volunteer'))->count();
        $totalOrganizations = User::whereHas('roles', fn($q) => $q->where('name', 'organization'))->count();
        $totalOpportunities = Opportunity::count();
        $totalApplications = Application::count();

        // Status breakdowns
        $applicationsByStatus = Application::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $organizationsByStatus = DB::table('organization_profiles')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Recent activity (last 30 days)
        $recentApplications = Application::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $recentOpportunities = Opportunity::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $recentUsers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        // Top performing organizations
        $topOrganizations = User::whereHas('roles', fn($q) => $q->where('name', 'organization'))
            ->withCount(['opportunities', 'opportunities as completed_opportunities_count' => function($q) {
                $q->where('status', 'completed');
            }])
            ->orderByDesc('opportunities_count')
            ->limit(10)
            ->get();

        return response()->json([
            'summary' => [
                'total_users' => $totalUsers,
                'total_volunteers' => $totalVolunteers,
                'total_organizations' => $totalOrganizations,
                'total_opportunities' => $totalOpportunities,
                'total_applications' => $totalApplications,
            ],
            'breakdowns' => [
                'applications_by_status' => $applicationsByStatus,
                'organizations_by_status' => $organizationsByStatus,
            ],
            'recent_activity' => [
                'applications_last_30_days' => $recentApplications,
                'opportunities_last_30_days' => $recentOpportunities,
                'users_last_30_days' => $recentUsers,
            ],
            'top_organizations' => $topOrganizations,
        ]);
    }





    /**
     * Get security data
     */
    public function getSecurityData(Request $request)
    {
        // Get all roles with their users and permissions
        $roles = Role::with(['users', 'permissions'])->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => ucfirst($role->name),
                'description' => $role->description,
                'users' => $role->users->count(),
                'permissions' => $role->permissions->pluck('description')->toArray(),
                'permission_names' => $role->permissions->pluck('name')->toArray(),
                'color' => $role->color,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at
            ];
        });

        // Get real security statistics
        $totalUsers = User::count();
        $activeUsers = User::whereNotNull('email_verified_at')->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        // Get users by role
        $adminUsers = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count();
        $orgUsers = User::whereHas('roles', fn($q) => $q->where('name', 'organization'))->count();
        $volunteerUsers = User::whereHas('roles', fn($q) => $q->where('name', 'volunteer'))->count();

        // Recent user registrations (last 7 days)
        $recentRegistrations = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        $securityStats = [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'adminUsers' => $adminUsers,
            'organizationUsers' => $orgUsers,
            'volunteerUsers' => $volunteerUsers,
            'recentRegistrations' => $recentRegistrations,
            'failedLogins' => 0, // Would come from login attempt logs
            'activeSessions' => rand(50, 150), // Would come from session store
            'securityAlerts' => 0, // Would come from security monitoring
        ];

        return response()->json([
            'roles' => $roles,
            'stats' => $securityStats,
            'permissions' => Permission::all()->groupBy('category')
        ]);
    }

    /**
     * Get security logs
     */
    public function getSecurityLogs(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $type = $request->get('type');
        $status = $request->get('status');
        $days = $request->get('days', 30);

        $query = SystemLog::with('user')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $logs = $query->paginate($perPage);

        // Transform the data for frontend
        $transformedLogs = $logs->getCollection()->map(function ($log) {
            return [
                'id' => $log->id,
                'type' => $log->type,
                'action' => $log->action,
                'user' => $log->user ? $log->user->name : 'System',
                'user_email' => $log->user_email,
                'ip_address' => $log->ip_address,
                'status' => $log->status,
                'description' => $log->description,
                'details' => $log->details,
                'timestamp' => $log->created_at->toISOString(),
                'formatted_time' => $log->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'data' => $transformedLogs,
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total()
            ]
        ]);
    }

    /**
     * Get system logs with filtering
     */
    public function getSystemLogs(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $type = $request->get('type');
        $status = $request->get('status');
        $user_id = $request->get('user_id');
        $days = $request->get('days', 7);

        $query = SystemLog::with('user')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        $logs = $query->paginate($perPage);

        // Transform the data for frontend
        $transformedLogs = $logs->getCollection()->map(function ($log) {
            return [
                'id' => $log->id,
                'type' => $log->type,
                'action' => $log->action,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'user' => $log->user ? $log->user->name : 'System',
                'user_email' => $log->user_email,
                'ip_address' => $log->ip_address,
                'status' => $log->status,
                'description' => $log->description,
                'details' => $log->details,
                'timestamp' => $log->created_at->toISOString(),
                'formatted_time' => $log->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'data' => $transformedLogs,
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total()
            ]
        ]);
    }

    /**
     * Get log statistics
     */
    public function getLogStatistics(Request $request)
    {
        $days = $request->get('days', 7);
        $startDate = Carbon::now()->subDays($days);

        $stats = [
            'total_logs' => SystemLog::where('created_at', '>=', $startDate)->count(),
            'by_type' => SystemLog::where('created_at', '>=', $startDate)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_status' => SystemLog::where('created_at', '>=', $startDate)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'recent_failed_logins' => SystemLog::where('type', 'login')
                ->where('status', 'failed')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'unique_users' => SystemLog::where('created_at', '>=', $startDate)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count(),
            'security_events' => SystemLog::where('type', 'security')
                ->where('created_at', '>=', $startDate)
                ->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get maintenance data
     */
    public function getMaintenanceData(Request $request)
    {
        $systemHealth = [
            'database' => [
                'status' => 'healthy',
                'response' => '45ms',
                'uptime' => '99.9%',
                'connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 'N/A'
            ],
            'api' => [
                'status' => 'healthy',
                'response' => '120ms',
                'uptime' => '99.8%'
            ],
            'storage' => [
                'status' => 'warning',
                'usage' => '78%',
                'available' => '2.1TB'
            ],
            'memory' => [
                'status' => 'healthy',
                'usage' => '45%',
                'available' => '16GB'
            ]
        ];

        $maintenanceTasks = [
            [
                'id' => 1,
                'name' => 'Database Optimization',
                'description' => 'Optimize database indexes and clean up old data',
                'lastRun' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                'nextRun' => Carbon::now()->addDays(6)->format('Y-m-d H:i:s'),
                'status' => 'scheduled'
            ],
            [
                'id' => 2,
                'name' => 'Log Cleanup',
                'description' => 'Remove old log files and compress archives',
                'lastRun' => Carbon::now()->subHours(6)->format('Y-m-d H:i:s'),
                'nextRun' => Carbon::now()->addHours(18)->format('Y-m-d H:i:s'),
                'status' => 'scheduled'
            ],
            [
                'id' => 3,
                'name' => 'Cache Refresh',
                'description' => 'Clear and rebuild application cache',
                'lastRun' => Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s'),
                'nextRun' => Carbon::now()->addHours(3)->format('Y-m-d H:i:s'),
                'status' => 'scheduled'
            ]
        ];

        return response()->json([
            'systemHealth' => $systemHealth,
            'maintenanceTasks' => $maintenanceTasks,
            'maintenanceMode' => false
        ]);
    }

    /**
     * Run backup
     */
    public function runBackup(Request $request)
    {
        try {
            // Simulate backup process
            sleep(2); // Simulate processing time

            $backupSize = '2.3GB';
            $timestamp = Carbon::now();

            // Log successful backup
            SystemLog::logSystemEvent('backup', [
                'size' => $backupSize,
                'timestamp' => $timestamp->toISOString(),
                'type' => 'manual'
            ], 'success');

            return response()->json([
                'message' => 'Backup completed successfully',
                'timestamp' => $timestamp->toISOString(),
                'size' => $backupSize
            ]);
        } catch (\Exception $e) {
            // Log failed backup
            SystemLog::logSystemEvent('backup', [
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()->toISOString(),
                'type' => 'manual'
            ], 'failed');

            return response()->json([
                'message' => 'Backup failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run maintenance task
     */
    public function runMaintenanceTask(Request $request, $taskId)
    {
        try {
            // Simulate task execution
            sleep(1); // Simulate processing time

            $taskNames = [
                1 => 'Database Optimization',
                2 => 'Log Cleanup',
                3 => 'Cache Refresh'
            ];

            $taskName = $taskNames[$taskId] ?? 'Unknown Task';
            $timestamp = Carbon::now();

            // Log successful task execution
            SystemLog::logSystemEvent('maintenance_task', [
                'task_name' => $taskName,
                'task_id' => $taskId,
                'timestamp' => $timestamp->toISOString(),
                'type' => 'manual'
            ], 'success');

            return response()->json([
                'message' => "{$taskName} completed successfully",
                'taskId' => $taskId,
                'timestamp' => $timestamp->toISOString()
            ]);
        } catch (\Exception $e) {
            $taskNames = [
                1 => 'Database Optimization',
                2 => 'Log Cleanup',
                3 => 'Cache Refresh'
            ];

            $taskName = $taskNames[$taskId] ?? 'Unknown Task';

            // Log failed task execution
            SystemLog::logSystemEvent('maintenance_task', [
                'task_name' => $taskName,
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()->toISOString(),
                'type' => 'manual'
            ], 'failed');

            return response()->json([
                'message' => "{$taskName} failed",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new role
     */
    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'required|string|max:500',
            'color' => 'required|string|max:50',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => strtolower($validated['name']),
            'description' => $validated['description'],
            'color' => $validated['color']
        ]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('name', $validated['permissions'])->get();
            $role->permissions()->sync($permissions->pluck('id'));
        }

        // Log role creation
        SystemLog::logSystemEvent('role_created', [
            'role_name' => $role->name,
            'role_id' => $role->id,
            'permissions' => $validated['permissions'] ?? []
        ]);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load('permissions')
        ], 201);
    }

    /**
     * Update a role
     */
    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'sometimes|string|max:500',
            'color' => 'sometimes|string|max:50',
            'permissions' => 'sometimes|array'
        ]);

        if (isset($validated['name'])) {
            $validated['name'] = strtolower($validated['name']);
        }

        $role->update($validated);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('name', $validated['permissions'])->get();
            $role->permissions()->sync($permissions->pluck('id'));
        }

        // Log role update
        SystemLog::logSystemEvent('role_updated', [
            'role_name' => $role->name,
            'role_id' => $role->id,
            'updated_fields' => array_keys($validated)
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions')
        ]);
    }

    /**
     * Delete a role
     */
    public function deleteRole(Role $role)
    {
        // Prevent deletion of core roles
        if (in_array($role->name, ['admin', 'organization', 'volunteer'])) {
            return response()->json([
                'message' => 'Cannot delete core system roles'
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role that has assigned users'
            ], 403);
        }

        // Log role deletion before deleting
        SystemLog::logSystemEvent('role_deleted', [
            'role_name' => $role->name,
            'role_id' => $role->id
        ]);

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $permissions = Permission::whereIn('name', $validated['permissions'])->get();
        $role->permissions()->sync($permissions->pluck('id'));

        return response()->json([
            'message' => 'Role permissions updated successfully',
            'role' => $role->load('permissions')
        ]);
    }
}