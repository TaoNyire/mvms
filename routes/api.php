<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\VolunteerSkillController;
use App\Http\Controllers\VolunteerProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\VolunteerDashboardController;
use App\Http\Controllers\OpportunityMatchingController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\OrganizationDashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrganizationReportController;
use App\Http\Controllers\VolunteerTaskController;
use App\Http\Controllers\TestController;


/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/

// Simple test route to check if API is working
Route::get('/health-check', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is working',
        'timestamp' => now()->toISOString()
    ]);
});

// Test public opportunities endpoint
Route::get('/test-public-opportunities', function () {
    try {
        $opportunities = \App\Models\Opportunity::with(['skills', 'organization'])
            ->where('status', 'active')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $opportunities->count(),
            'data' => $opportunities,
            'message' => 'Public opportunities test endpoint working'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Removed duplicate route - using controller-based route instead

// Public stats endpoint (no authentication required)
Route::get('/stats/public', function () {
    try {
        $volunteerCount = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'volunteer');
        })->count();

        $organizationCount = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'organization');
        })->count();

        $opportunityCount = \App\Models\Opportunity::count();

        return response()->json([
            'volunteers' => $volunteerCount,
            'organizations' => $organizationCount,
            'opportunities' => $opportunityCount,
            'message' => 'Real stats from database'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'volunteers' => 0,
            'organizations' => 0,
            'opportunities' => 0,
            'error' => 'Database error: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Get current authenticated user
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

/*
|--------------------------------------------------------------------------
| Volunteer Routes (Requires Volunteer Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:volunteer'])->group(function () {
    // Dashboard
    Route::get('/volunteer/dashboard', [VolunteerDashboardController::class, 'index']);

    // Profile management
    Route::get('/volunteer/profile', [VolunteerProfileController::class, 'show']);
    Route::post('/volunteer/profile', [VolunteerProfileController::class, 'storeOrUpdate']);

    // Skill management
    Route::post('/volunteer/skills', [VolunteerSkillController::class, 'update']);

    // Recommended/matched opportunities
    Route::get('/volunteer/recommended', [OpportunityMatchingController::class, 'recommendedForVolunteer']);

    // Volunteer applies and views their applications
    Route::post('/opportunities/{opportunity}/apply', [ApplicationController::class, 'apply']);
    Route::get('/my-applications', [ApplicationController::class, 'myApplications']);

    // Volunteer can view their task status for an application
    Route::get('/applications/{application}/task-status', [TaskStatusController::class, 'applicationStatus']);

    // Volunteer feedback to organisation
    Route::post('/applications/{application}/org-feedback', [FeedbackController::class, 'submitByVolunteer']);
    Route::get('/my-feedback', [FeedbackController::class, 'myFeedback']);

    // Volunteer notifications
    Route::get('/my-notifications', [VolunteerDashboardController::class, 'getNotifications']);
    Route::put('/notifications/{id}/read', [VolunteerDashboardController::class, 'markNotificationAsRead']);

    // Volunteer withdraws an application
    Route::delete('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw']);

    // Volunteer confirms or rejects accepted application
    Route::post('/applications/{application}/confirm', [ApplicationController::class, 'confirm']);

    // Messaging
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/contacts', [MessageController::class, 'getContacts']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/{partnerId}', [MessageController::class, 'getMessages']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead']);
});

/*
|--------------------------------------------------------------------------
| Organization Routes (Requires Organization Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:organization'])->group(function () {
    // Dashboard
    Route::get('/organization/dashboard', [OrganizationDashboardController::class, 'index']);
    Route::get('/organization/reports', [OrganizationDashboardController::class, 'reports']);

    // Profile management
    Route::get('/organization/profile', [OrganizationProfileController::class, 'show']);
    Route::post('/organization/profile', [OrganizationProfileController::class, 'storeOrUpdate']);

    // Opportunity management
    Route::get('/opportunities', [OpportunityController::class, 'index']);
    Route::get('/opportunities/{id}', [OpportunityController::class, 'show']);
    Route::get('/opportunities/{id}/applications', [OpportunityController::class, 'getApplications']);
    Route::post('/opportunities/add', [OpportunityController::class, 'store']);
    Route::put('/opportunities/{opportunity}', [OpportunityController::class, 'update']);
    Route::delete('/opportunities/{opportunity}', [OpportunityController::class, 'destroy']);

    // Organization views and responds to applications
    Route::get('/applications', [ApplicationController::class, 'orgApplications']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::put('/applications/{application}/respond', [ApplicationController::class, 'respond']);

    // Task management
    // Set task status (completed/quit) -- only org/admin
    Route::post('/applications/{application}/task-status', [TaskStatusController::class, 'setStatus']);

    // View task status for an application
    Route::get('/applications/{application}/task-status', [TaskStatusController::class, 'applicationStatus']);

    // List currently working volunteers
    Route::get('/org/current-volunteers', [TaskStatusController::class, 'currentVolunteers']);

    // List recently assigned volunteers
    Route::get('/org/recent-volunteers', [TaskStatusController::class, 'recentVolunteers']);

    // List all volunteers & statuses for a given opportunity
    Route::get('/org/opportunities/{opportunity}/volunteer-tasks', [TaskStatusController::class, 'volunteerTasks']);

    // Handle feedback:
    Route::post('/applications/{application}/feedback', [FeedbackController::class, 'submitByOrg']);
    Route::get('/organization/feedback-received', [FeedbackController::class, 'orgFeedbackHistory']);

    // Messaging (same routes as volunteers)
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/contacts', [MessageController::class, 'getContacts']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/{partnerId}', [MessageController::class, 'getMessages']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Requires Admin Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

    // Security
    Route::get('/admin/security', [AdminDashboardController::class, 'getSecurityData']);
    Route::get('/admin/security/logs', [AdminDashboardController::class, 'getSecurityLogs']);
    Route::post('/admin/roles', [AdminDashboardController::class, 'createRole']);
    Route::put('/admin/roles/{role}', [AdminDashboardController::class, 'updateRole']);
    Route::delete('/admin/roles/{role}', [AdminDashboardController::class, 'deleteRole']);
    Route::post('/admin/roles/{role}/permissions', [AdminDashboardController::class, 'updateRolePermissions']);

    // System Logs
    Route::get('/admin/logs', [AdminDashboardController::class, 'getSystemLogs']);
    Route::get('/admin/logs/statistics', [AdminDashboardController::class, 'getLogStatistics']);

    // Skills Management (Admin)
    Route::apiResource('skills', SkillController::class);

    // Maintenance
    Route::get('/admin/maintenance', [AdminDashboardController::class, 'getMaintenanceData']);
    Route::post('/admin/maintenance/backup', [AdminDashboardController::class, 'runBackup']);
    Route::post('/admin/maintenance/task/{taskId}', [AdminDashboardController::class, 'runMaintenanceTask']);

    // Skill management
    Route::get('/skills', [SkillController::class, 'index']);
    Route::post('/skills/addskill', [SkillController::class, 'store']);
    Route::put('/skills/updateskill/{skill}', [SkillController::class, 'update']);
    Route::delete('/skills/deleteskill/{skill}', [SkillController::class, 'destroy']);

     // Organization Management
    Route::get('organizations', [OrganizationController::class, 'index']);
    Route::get('organizations/{id}', [OrganizationController::class, 'show']);
    Route::put('organizations/{id}/approve', [OrganizationController::class, 'approve']);
    Route::put('organizations/{id}/reject', [OrganizationController::class, 'reject']);
    Route::put('organizations/{id}/toggle-active', [OrganizationController::class, 'toggleActive']);
    Route::delete('organizations/{id}', [OrganizationController::class, 'destroy']);

    // User Management
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}/toggle-active', [UserController::class, 'toggleActive']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::post('users/add-volunteer', [UserController::class, 'addVolunteer']);
    Route::post('users/add-organization', [UserController::class, 'addOrganization']);

    // Organization Management
    Route::get('organizations', [OrganizationController::class, 'index']);
    Route::get('organizations/{id}', [OrganizationController::class, 'show']);
    Route::post('organizations/{id}/approve', [OrganizationController::class, 'approve']);
    Route::post('organizations/{id}/reject', [OrganizationController::class, 'reject']);

    // Admin Applications Management
    Route::get('admin/applications', [ApplicationController::class, 'adminIndex']);
    Route::put('admin/applications/{application}/status', [ApplicationController::class, 'updateStatus']);

    // Admin Opportunities Management
    Route::get('admin/opportunities', [OpportunityController::class, 'adminIndex']);

    // Admin Feedback Management
    Route::get('admin/feedback', [FeedbackController::class, 'adminIndex']);

    // Admin Reports
    Route::get('admin/reports', [AdminDashboardController::class, 'reports']);
});

/*
|--------------------------------------------------------------------------
| Public Routes (Accessible to Anyone) - MUST BE FIRST
|--------------------------------------------------------------------------
*/

// Public opportunities endpoint - NO AUTHENTICATION REQUIRED
Route::get('/opportunities/public', [OpportunityMatchingController::class, 'publicIndex']);

// Alternative public opportunities endpoint for testing
Route::get('/public/opportunities', [OpportunityMatchingController::class, 'publicIndex']);

// Test skills endpoint (with auth)
Route::middleware('auth:sanctum')->get('/test-my-skills', function (Request $request) {
    try {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'skills' => [],
            'message' => 'Test skills endpoint working'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Test add skill endpoint
Route::middleware('auth:sanctum')->post('/test-add-skill', function (Request $request) {
    try {
        $user = $request->user();

        // Check if skills table has data
        $skillsCount = \App\Models\Skill::count();
        $firstSkill = \App\Models\Skill::first();

        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'skills_count' => $skillsCount,
            'first_skill' => $firstSkill,
            'request_data' => $request->all(),
            'message' => 'Test add skill endpoint working'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test opportunity endpoint (no auth required)
Route::get('/test/opportunity/{id}', function($id) {
    try {
        $opportunity = \App\Models\Opportunity::with(['skills', 'organization'])->find($id);
        if (!$opportunity) {
            return response()->json(['error' => 'Opportunity not found'], 404);
        }
        return response()->json(['data' => $opportunity]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Test route for debugging
Route::get('/test-messages', function () {
    $messageCount = \App\Models\Message::count();
    $userCount = \App\Models\User::count();
    return response()->json([
        'status' => 'API is working',
        'message_count' => $messageCount,
        'user_count' => $userCount,
        'timestamp' => now()
    ]);
});

// Test auth route for debugging
Route::middleware('auth:sanctum')->get('/test-auth', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'status' => 'Authenticated',
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'roles' => $user->roles->pluck('name'),
        'has_volunteer_role' => $user->hasRole('volunteer'),
        'has_organization_role' => $user->hasRole('organization'),
        'has_admin_role' => $user->hasRole('admin'),
        'volunteer_profile_exists' => $user->volunteerProfile ? true : false,
        'organization_profile_exists' => $user->organizationProfile ? true : false,
        'timestamp' => now()
    ]);
});

// Temporary endpoint to assign organization role to current user
Route::middleware('auth:sanctum')->post('/assign-organization-role', function (Request $request) {
    $user = $request->user();
    $organizationRole = \App\Models\Role::where('name', 'organization')->first();

    if (!$organizationRole) {
        return response()->json(['error' => 'Organization role not found'], 404);
    }

    // Check if user already has organization role
    if ($user->hasRole('organization')) {
        return response()->json(['message' => 'User already has organization role', 'user' => $user->load('roles')]);
    }

    // Assign organization role
    $user->roles()->attach($organizationRole->id);

    return response()->json([
        'message' => 'Organization role assigned successfully',
        'user' => $user->load('roles')
    ]);
});

// Temporary endpoint to assign volunteer role to current user
Route::middleware('auth:sanctum')->post('/assign-volunteer-role', function (Request $request) {
    $user = $request->user();
    $volunteerRole = \App\Models\Role::where('name', 'volunteer')->first();

    if (!$volunteerRole) {
        return response()->json(['error' => 'Volunteer role not found'], 404);
    }

    // Check if user already has volunteer role
    if ($user->hasRole('volunteer')) {
        return response()->json(['message' => 'User already has volunteer role', 'user' => $user->load('roles')]);
    }

    // Assign volunteer role
    $user->roles()->attach($volunteerRole->id);

    return response()->json([
        'message' => 'Volunteer role assigned successfully',
        'user' => $user->load('roles')
    ]);
});

// Alternative messaging routes without role restrictions (just auth required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages-alt/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages-alt/contacts', [MessageController::class, 'getContacts']);
    Route::get('/messages-alt/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages-alt/{partnerId}', [MessageController::class, 'getMessages']);
    Route::post('/messages-alt/send', [MessageController::class, 'sendMessage']);
    Route::put('/messages-alt/{message}/read', [MessageController::class, 'markAsRead']);

    // Alternative feedback routes without role restrictions
    Route::post('/applications/{application}/feedback-alt', [FeedbackController::class, 'submitByOrg']);
    Route::post('/applications/{application}/org-feedback-alt', [FeedbackController::class, 'submitByVolunteer']);
    Route::get('/my-feedback-alt', [FeedbackController::class, 'myFeedback']);
    Route::get('/organization/feedback-received-alt', [FeedbackController::class, 'orgFeedbackHistory']);
    Route::get('/applications/{application}/feedback-status', [FeedbackController::class, 'getFeedbackStatus']);

    // Alternative organization reports route
    Route::get('/organization/reports-alt', [OrganizationDashboardController::class, 'reports']);

    // Organization Reports
    Route::get('/organization/reports', [OrganizationReportController::class, 'getReports']);
    Route::get('/organization/reports/export', [OrganizationReportController::class, 'exportReport']);

    // Organization Task Management
    Route::post('/organization/applications/{applicationId}/task-status', [OrganizationReportController::class, 'updateTaskStatus']);
    Route::get('/organization/applications', [OrganizationReportController::class, 'getApplications']);

    // Test endpoints
    Route::get('/test', [TestController::class, 'test']);
    Route::get('/test/applications', [TestController::class, 'getApplications']);

    // Skills for all authenticated users
    Route::get('/skills', [SkillController::class, 'index']);
    Route::get('/my-skills', [SkillController::class, 'getUserSkills']);
    Route::post('/my-skills', [SkillController::class, 'addUserSkill']);
    Route::put('/my-skills/{skillId}', [SkillController::class, 'updateUserSkill']);
    Route::delete('/my-skills/{skillId}', [SkillController::class, 'removeUserSkill']);
    Route::get('/skill-matches', [SkillController::class, 'getSkillMatches']);
    Route::post('/recalculate-matches', [SkillController::class, 'recalculateMatches']);

    // Volunteer Task Management
    Route::get('/my-tasks', [VolunteerTaskController::class, 'getMyTasks']);
    Route::post('/tasks/{applicationId}/start', [VolunteerTaskController::class, 'startTask']);
    Route::post('/tasks/{applicationId}/complete', [VolunteerTaskController::class, 'completeTask']);
    Route::post('/tasks/{applicationId}/quit', [VolunteerTaskController::class, 'quitTask']);
    Route::put('/tasks/{applicationId}/progress', [VolunteerTaskController::class, 'updateProgress']);
});

// Test authenticated user
Route::middleware('auth:sanctum')->get('/test-auth', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'authenticated' => true,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'roles' => $user->roles->pluck('name'),
        'has_volunteer_role' => $user->hasRole('volunteer'),
        'volunteer_profile_exists' => $user->volunteerProfile ? true : false,
        'timestamp' => now()
    ]);
});

// Test volunteer endpoints without role middleware
Route::middleware('auth:sanctum')->get('/test-volunteer-dashboard', [VolunteerDashboardController::class, 'index']);
Route::middleware('auth:sanctum')->get('/test-volunteer-profile', [VolunteerProfileController::class, 'show']);
Route::middleware('auth:sanctum')->get('/test-volunteer-recommended', [OpportunityMatchingController::class, 'recommendedForVolunteer']);

// Debug skills endpoint
Route::get('/debug-skills', function () {
    try {
        $skillsCount = \App\Models\Skill::count();
        $userSkillsCount = \DB::table('user_skills')->count();
        $usersCount = \App\Models\User::count();

        // Check if skills table has required columns
        $skillsColumns = \Schema::getColumnListing('skills');
        $userSkillsColumns = \Schema::getColumnListing('user_skills');

        return response()->json([
            'status' => 'success',
            'skills_count' => $skillsCount,
            'user_skills_count' => $userSkillsCount,
            'users_count' => $usersCount,
            'skills_columns' => $skillsColumns,
            'user_skills_columns' => $userSkillsColumns,
            'message' => 'Database debug info'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test database data
Route::get('/test-db', function () {
    $userCount = \App\Models\User::count();
    $volunteerCount = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'volunteer'); })->count();
    $roleCount = \App\Models\Role::count();
    $roles = \App\Models\Role::pluck('name');
    $volunteers = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'volunteer'); })
        ->with('roles')
        ->select('id', 'name', 'email')
        ->get();

    return response()->json([
        'total_users' => $userCount,
        'volunteer_users' => $volunteerCount,
        'total_roles' => $roleCount,
        'available_roles' => $roles,
        'sample_user' => \App\Models\User::with('roles')->first(),
        'volunteer_list' => $volunteers,
    ]);
});

// Temporary endpoint to assign volunteer role to current user
Route::middleware('auth:sanctum')->post('/assign-volunteer-role', function (Request $request) {
    $user = $request->user();
    $volunteerRole = \App\Models\Role::where('name', 'volunteer')->first();

    if (!$volunteerRole) {
        return response()->json(['error' => 'Volunteer role not found'], 404);
    }

    // Check if user already has volunteer role
    if ($user->hasRole('volunteer')) {
        return response()->json(['message' => 'User already has volunteer role', 'user' => $user->load('roles')]);
    }

    // Assign volunteer role
    $user->roles()->attach($volunteerRole->id);

    return response()->json([
        'message' => 'Volunteer role assigned successfully',
        'user' => $user->load('roles')
    ]);
});

// Get volunteer login credentials for testing
Route::get('/volunteer-credentials', function () {
    $volunteers = \App\Models\User::whereHas('roles', function($q) {
        $q->where('name', 'volunteer');
    })->select('id', 'name', 'email')->take(3)->get();

    return response()->json([
        'message' => 'Sample volunteer accounts (password is likely "password" for seeded users)',
        'volunteers' => $volunteers,
        'note' => 'Try logging in with any of these email addresses and password "password"'
    ]);
});

// Removed duplicate stats route - using the one defined earlier