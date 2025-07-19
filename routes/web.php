<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'webLogin'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'webRegister'])->name('register.submit');

// Password Reset routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected routes
Route::middleware('auth:web')->group(function () {
    // General dashboard (fallback)
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

    // Secure shared routes - require authentication and proper role verification (excluding admins)
    Route::middleware(['verified.user.role', 'exclude.admin'])->group(function () {
        // Notifications - accessible to authenticated users with verified roles (excluding admins)
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/{notification}/unread', [\App\Http\Controllers\NotificationController::class, 'markAsUnread'])->name('notifications.unread');
        Route::post('/notifications/{notification}/archive', [\App\Http\Controllers\NotificationController::class, 'archive'])->name('notifications.archive');
        Route::post('/notifications/{notification}/unarchive', [\App\Http\Controllers\NotificationController::class, 'unarchive'])->name('notifications.unarchive');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/notifications/bulk-action', [\App\Http\Controllers\NotificationController::class, 'bulkAction'])->name('notifications.bulk-action');
        Route::get('/notifications/preferences', [\App\Http\Controllers\NotificationController::class, 'preferences'])->name('notifications.preferences');
        Route::post('/notifications/preferences', [\App\Http\Controllers\NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');
        Route::post('/notifications/preferences/reset', [\App\Http\Controllers\NotificationController::class, 'resetPreferences'])->name('notifications.preferences.reset');

        // Messages - secure messaging between authenticated users (excluding admins)
        Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
        Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
        Route::get('/messages/{conversation}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/send', [\App\Http\Controllers\MessageController::class, 'sendMessage'])->name('messages.send');
        Route::post('/messages/{message}/react', [\App\Http\Controllers\MessageController::class, 'react'])->name('messages.react');
        Route::delete('/messages/{message}/react', [\App\Http\Controllers\MessageController::class, 'removeReaction'])->name('messages.remove-reaction');
        Route::post('/messages/{conversation}/archive', [\App\Http\Controllers\MessageController::class, 'archive'])->name('messages.archive');
        Route::get('/messages/unread-count', [\App\Http\Controllers\MessageController::class, 'unreadCount'])->name('messages.unread-count');

        // Announcements - view access for authenticated users (excluding admins)
        Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'show'])->name('announcements.show');
        Route::post('/announcements/{announcement}/like', [\App\Http\Controllers\AnnouncementController::class, 'toggleLike'])->name('announcements.like');
    });
});

// Role-specific protected routes
Route::middleware(['auth:web', 'web.role:admin'])->group(function () {
    // Admin Reports - Export Users
    Route::get('/admin/reports/export-users', [\App\Http\Controllers\Admin\AdminReportController::class, 'exportUsers'])->name('admin.reports.export-users');
    // Admin Dashboard
    Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');


    Route::get('/admin/logs', [\App\Http\Controllers\Admin\AdminController::class, 'logs'])->name('admin.logs');

    // Admin Profile Management
    Route::get('/admin/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'show'])->name('admin.profile.show');
    Route::get('/admin/profile/edit', [\App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('/admin/profile/change-password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'showChangePasswordForm'])->name('admin.profile.change-password');
    Route::post('/admin/profile/change-password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'changePassword'])->name('admin.profile.change-password');
    Route::get('/admin/profile/security', [\App\Http\Controllers\Admin\AdminProfileController::class, 'security'])->name('admin.profile.security');

    // User Management
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/{user}/activate', [\App\Http\Controllers\Admin\UserManagementController::class, 'activate'])->name('admin.users.activate');
    Route::post('/admin/users/{user}/deactivate', [\App\Http\Controllers\Admin\UserManagementController::class, 'deactivate'])->name('admin.users.deactivate');
    Route::post('/admin/users/{user}/suspend', [\App\Http\Controllers\Admin\UserManagementController::class, 'suspend'])->name('admin.users.suspend');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/bulk-action', [\App\Http\Controllers\Admin\UserManagementController::class, 'bulkAction'])->name('admin.users.bulk-action');
    Route::post('/admin/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');

    // Organization Approval
    Route::get('/admin/organizations', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'index'])->name('admin.organizations.index');
    Route::get('/admin/organizations/{organization}', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'show'])->name('admin.organizations.show');
    Route::post('/admin/organizations/{organization}/approve', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'approve'])->name('admin.organizations.approve');
    Route::post('/admin/organizations/{organization}/reject', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'reject'])->name('admin.organizations.reject');
    Route::post('/admin/organizations/{organization}/request-info', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'requestInfo'])->name('admin.organizations.request-info');
    Route::post('/admin/organizations/{organization}/suspend', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'suspend'])->name('admin.organizations.suspend');
    Route::post('/admin/organizations/{organization}/reactivate', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'reactivate'])->name('admin.organizations.reactivate');
    Route::post('/admin/organizations/bulk-action', [\App\Http\Controllers\Admin\OrganizationApprovalController::class, 'bulkAction'])->name('admin.organizations.bulk-action');
});

Route::middleware(['auth:web', 'web.role:organization'])->group(function () {
    // Organization profile creation routes (accessible without profile completion)
    Route::get('/organization/profile/create', [\App\Http\Controllers\OrganizationProfileWebController::class, 'create'])->name('organization.profile.create');
    Route::post('/organization/profile', [\App\Http\Controllers\OrganizationProfileWebController::class, 'store'])->name('organization.profile.store');
    Route::get('/organization/profile', [\App\Http\Controllers\OrganizationProfileWebController::class, 'show'])->name('organization.profile.show');
    Route::put('/organization/profile', [\App\Http\Controllers\OrganizationProfileWebController::class, 'update'])->name('organization.profile.update');

    // Quick test route for organization profile completion
    Route::get('/organization/profile/quick-complete', function() {
        $user = Auth::user();
        $profile = $user->organizationProfile;

        if (!$profile) {
            $profile = \App\Models\OrganizationProfile::create(['user_id' => $user->id]);
        }

        $profile->update([
            'org_name' => $user->name,
            'description' => 'Test organization profile created for testing purposes.',
            'mission' => 'To serve the community through volunteer coordination.',
            'sector' => 'Community',
            'org_type' => 'NGO',
            'physical_address' => 'Test Address, Lilongwe',
            'district' => 'Lilongwe',
            'region' => 'Central',
            'email' => $user->email,
            'phone' => '+265 123 456 789',
            'focus_areas' => ['Community Development', 'Volunteer Coordination'],
            'contact_person_name' => $user->name,
            'contact_person_email' => $user->email,
            'is_complete' => true,
            'profile_completed_at' => now(),
            'status' => 'approved'
        ]);

        return redirect()->route('organization.dashboard')->with('success', 'Organization profile completed successfully!');
    })->name('organization.profile.quick-complete');

    // Organization dashboard (accessible without profile completion but with warnings)
    Route::get('/organization/dashboard', [AuthController::class, 'organizationDashboard'])->name('organization.dashboard');

    // Organization communication routes (now handled in shared section above)

    // Message routes (now handled in shared section above)

    // Debug route
    Route::get('/debug/user', function() {
        $user = Auth::user();
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'hasRole_organization' => $user->hasRole('organization'),
            'hasRole_volunteer' => $user->hasRole('volunteer'),
            'hasRole_admin' => $user->hasRole('admin'),
        ];
    });

    // Test log generation route
    Route::get('/debug/generate-logs', function() {
        \Log::info('Test log entry generated', ['user' => Auth::user()->name, 'time' => now()]);
        \Log::warning('Test warning message', ['level' => 'warning']);
        \Log::error('Test error message', ['level' => 'error']);
        return 'Log entries generated successfully!';
    });

    // Test route for announcement create
    Route::get('/test-announcement-create', function() {
        return 'Announcement create route is accessible! User: ' . Auth::user()->name . ' Role: ' . (Auth::user()->hasRole('organization') ? 'organization' : 'not organization');
    });

    // Organization-specific announcement routes (create, pin, unpin - view routes are in shared section)
    Route::get('/announcements/create', [\App\Http\Controllers\AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/announcements/{announcement}/pin', [\App\Http\Controllers\AnnouncementController::class, 'pin'])->name('announcements.pin');
    Route::post('/announcements/{announcement}/unpin', [\App\Http\Controllers\AnnouncementController::class, 'unpin'])->name('announcements.unpin');

    // Protected organization routes (require profile completion)
    Route::middleware('organization.profile.complete')->group(function () {

        // Organization opportunity management
        Route::resource('opportunities', \App\Http\Controllers\OpportunityController::class);
        Route::post('/opportunities/{opportunity}/publish', [\App\Http\Controllers\OpportunityController::class, 'publish'])->name('opportunities.publish');
        Route::post('/opportunities/{opportunity}/pause', [\App\Http\Controllers\OpportunityController::class, 'pause'])->name('opportunities.pause');
        Route::post('/opportunities/{opportunity}/complete', [\App\Http\Controllers\OpportunityController::class, 'complete'])->name('opportunities.complete');

        // Organization application management
        Route::get('/organization/applications', [\App\Http\Controllers\ApplicationController::class, 'organizationApplications'])->name('organization.applications.index');
        Route::post('/applications/{application}/accept', [\App\Http\Controllers\ApplicationController::class, 'accept'])->name('applications.accept');
        Route::post('/applications/{application}/reject', [\App\Http\Controllers\ApplicationController::class, 'reject'])->name('applications.reject');
        Route::post('/applications/bulk-action', [\App\Http\Controllers\ApplicationController::class, 'bulkAction'])->name('applications.bulk-action');

        // Application details
        Route::get('/applications/{application}', [\App\Http\Controllers\ApplicationController::class, 'show'])->name('applications.show');

        // Task management
        Route::get('/opportunities/{opportunity}/tasks', [\App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
        Route::get('/opportunities/{opportunity}/tasks/create', [\App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
        Route::post('/opportunities/{opportunity}/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
        Route::get('/opportunities/{opportunity}/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
        Route::get('/opportunities/{opportunity}/tasks/{task}/edit', [\App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/opportunities/{opportunity}/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/opportunities/{opportunity}/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/opportunities/{opportunity}/tasks/{task}/publish', [\App\Http\Controllers\TaskController::class, 'publish'])->name('tasks.publish');
        Route::post('/opportunities/{opportunity}/tasks/{task}/complete', [\App\Http\Controllers\TaskController::class, 'complete'])->name('tasks.complete');
        Route::post('/opportunities/{opportunity}/tasks/{task}/cancel', [\App\Http\Controllers\TaskController::class, 'cancel'])->name('tasks.cancel');

        // Assignment management
        Route::post('/tasks/{task}/assign', [\App\Http\Controllers\AssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/tasks/{task}/bulk-assign', [\App\Http\Controllers\AssignmentController::class, 'bulkAssign'])->name('assignments.bulk-assign');
        Route::post('/assignments/{assignment}/cancel', [\App\Http\Controllers\AssignmentController::class, 'cancel'])->name('assignments.cancel');
        Route::post('/assignments/{assignment}/no-show', [\App\Http\Controllers\AssignmentController::class, 'markNoShow'])->name('assignments.no-show');
        Route::post('/assignments/{assignment}/resolve-conflict', [\App\Http\Controllers\AssignmentController::class, 'resolveConflict'])->name('assignments.resolve-conflict');

        // Organization calendar and scheduling
        Route::get('/organization/calendar', [\App\Http\Controllers\CalendarController::class, 'organizationCalendar'])->name('organization.calendar');
        Route::get('/organization/calendar/events', [\App\Http\Controllers\CalendarController::class, 'organizationCalendar'])->name('organization.calendar.events');
        Route::post('/calendar/check-conflicts', [\App\Http\Controllers\CalendarController::class, 'checkConflicts'])->name('calendar.check-conflicts');
        Route::get('/calendar/available-volunteers', [\App\Http\Controllers\CalendarController::class, 'getAvailableVolunteers'])->name('calendar.available-volunteers');

        // Organization Reports
        Route::get('/organization/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('organization.reports.index');
        Route::post('/organization/reports/preview', [\App\Http\Controllers\ReportController::class, 'preview'])->name('organization.reports.preview');
        Route::get('/organization/reports/volunteers', [\App\Http\Controllers\ReportController::class, 'monthlyRecruitedVolunteers'])->name('organization.reports.volunteers');
        Route::get('/organization/reports/completed-tasks', [\App\Http\Controllers\ReportController::class, 'monthlyCompletedTasks'])->name('organization.reports.completed');
        Route::get('/organization/reports/failed-tasks', [\App\Http\Controllers\ReportController::class, 'monthlyFailedTasks'])->name('organization.reports.failed');
        Route::get('/organization/reports/comprehensive', [\App\Http\Controllers\ReportController::class, 'monthlyComprehensiveReport'])->name('organization.reports.comprehensive');

        // Organization Task Management
        Route::prefix('organization/opportunities/{opportunity}')->name('organization.opportunities.')->group(function () {
            Route::get('/tasks', function(\App\Models\Opportunity $opportunity) {
                try {
                    // Get tasks with basic relationships
                    $tasks = $opportunity->tasks()->orderBy('created_at', 'desc')->paginate(15);

                    // Calculate basic stats
                    $allTasks = $opportunity->tasks();
                    $stats = [
                        'total_tasks' => $allTasks->count(),
                        'active_tasks' => $allTasks->where('status', 'published')->count(),
                        'completed_tasks' => $allTasks->where('status', 'completed')->count(),
                        'overdue_tasks' => 0, // Simplified for now
                        'total_assignments' => 0, // Simplified for now
                        'pending_assignments' => 0, // Simplified for now
                    ];

                    return view('organization.tasks.index', compact('opportunity', 'tasks', 'stats'));
                } catch (\Exception $e) {
                    // Return a simple error page instead of JSON
                    return view('errors.500')->with('error', $e->getMessage());
                }
            })->name('tasks.index');

            Route::get('/tasks/create', function(\App\Models\Opportunity $opportunity) {
                try {
                    $availableVolunteers = \App\Models\User::whereHas('applications', function($query) use ($opportunity) {
                        $query->where('opportunity_id', $opportunity->id)->where('status', 'accepted');
                    })->with('volunteerProfile')->get();
                    return view('organization.tasks.create', compact('opportunity', 'availableVolunteers'));
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            })->name('tasks.create');

            Route::post('/tasks', function(\App\Models\Opportunity $opportunity, \Illuminate\Http\Request $request) {
                try {
                    $validated = $request->validate([
                        'title' => 'required|string|max:255',
                        'description' => 'required|string',
                        'start_datetime' => 'required|date|after:now',
                        'end_datetime' => 'required|date|after:start_datetime',
                        'location_type' => 'required|in:physical,remote,hybrid',
                        'location_address' => 'nullable|string|max:500',
                        'volunteers_needed' => 'required|integer|min:1|max:50',
                        'priority' => 'required|in:low,medium,high,urgent',
                    ]);

                    $task = \App\Models\Task::create([
                        'opportunity_id' => $opportunity->id,
                        'created_by' => Auth::id(),
                        'status' => 'draft',
                        'volunteers_assigned' => 0,
                        ...$validated
                    ]);

                    return redirect()
                        ->route('organization.opportunities.tasks.index', $opportunity)
                        ->with('success', "Task '{$task->title}' has been created successfully! You can now assign volunteers to this task.");

                } catch (\Illuminate\Validation\ValidationException $e) {
                    return back()->withErrors($e->validator)->withInput();
                } catch (\Exception $e) {
                    \Log::error('Task creation failed', [
                        'opportunity_id' => $opportunity->id,
                        'user_id' => Auth::id(),
                        'error' => $e->getMessage()
                    ]);

                    return back()
                        ->with('error', 'Failed to create task. Please try again or contact support if the problem persists.')
                        ->withInput();
                }
            })->name('tasks.store');
        });
    });
});

Route::middleware(['auth:web', 'web.role:volunteer'])->group(function () {
    // Profile creation routes (accessible without profile completion)
    Route::get('/volunteer/profile/create', [\App\Http\Controllers\VolunteerProfileWebController::class, 'create'])->name('volunteer.profile.create');
    Route::post('/volunteer/profile', [\App\Http\Controllers\VolunteerProfileWebController::class, 'store'])->name('volunteer.profile.store');
    Route::get('/volunteer/profile', [\App\Http\Controllers\VolunteerProfileWebController::class, 'show'])->name('volunteer.profile.show');
    Route::get('/volunteer/profile/edit', [\App\Http\Controllers\VolunteerProfileWebController::class, 'edit'])->name('volunteer.profile.edit');
    Route::put('/volunteer/profile', [\App\Http\Controllers\VolunteerProfileWebController::class, 'update'])->name('volunteer.profile.update');



    // Quick test route for profile completion
    Route::get('/volunteer/profile/quick-complete', function() {
        $user = Auth::user();
        $profile = $user->volunteerProfile;

        if (!$profile) {
            $profile = \App\Models\VolunteerProfile::create(['user_id' => $user->id]);
        }

        $profile->update([
            'full_name' => $user->name,
            'phone' => '+265 123 456 789',
            'bio' => 'Test volunteer profile created for testing purposes.',
            'physical_address' => 'Test Address, Lilongwe',
            'district' => 'Lilongwe',
            'region' => 'Central',
            'education_level' => 'Degree',
            'motivation' => 'I want to help my community through volunteer work.',
            'skills' => ['Teaching', 'Technology'],
            'available_days' => ['monday', 'tuesday'],
            'availability_type' => 'flexible',
            'is_complete' => true,
            'profile_completed_at' => now()
        ]);

        return redirect()->route('volunteer.dashboard')->with('success', 'Profile completed successfully!');
    })->name('volunteer.profile.quick-complete');

    // Volunteer Task Management
    Route::prefix('volunteer/tasks')->name('volunteer.tasks.')->group(function () {
        Route::get('/', function() {
            $assignments = \App\Models\Assignment::where('volunteer_id', Auth::id())
                ->with(['task.opportunity.organization', 'assignedBy'])
                ->orderBy('scheduled_start')->paginate(15);
            $stats = [
                'total' => \App\Models\Assignment::where('volunteer_id', Auth::id())->count(),
                'pending' => \App\Models\Assignment::where('volunteer_id', Auth::id())->where('status', 'pending')->count(),
                'accepted' => \App\Models\Assignment::where('volunteer_id', Auth::id())->where('status', 'accepted')->count(),
                'completed' => \App\Models\Assignment::where('volunteer_id', Auth::id())->where('status', 'completed')->count(),
                'upcoming' => \App\Models\Assignment::where('volunteer_id', Auth::id())->where('status', 'accepted')->where('scheduled_start', '>', now())->count(),
            ];
            return view('volunteer.tasks.index', compact('assignments', 'stats'))->with(['status' => 'all', 'priority' => 'all']);
        })->name('index');

        Route::get('/browse', function() {
            $availableTasks = \App\Models\Task::where('status', 'active')
                ->where('allow_self_assignment', true)
                ->where('assignment_deadline', '>', now())
                ->whereColumn('volunteers_assigned', '<', 'volunteers_needed')
                ->whereHas('opportunity', function($q) {
                    $q->where('status', 'published');
                })
                ->whereHas('opportunity.applications', function($q) {
                    $q->where('volunteer_id', Auth::id())->where('status', 'accepted');
                })
                ->with(['opportunity.organization', 'assignments'])
                ->orderBy('start_datetime')->paginate(12);
            return view('volunteer.tasks.browse', compact('availableTasks'))->with(['category' => 'all', 'priority' => 'all', 'location' => 'all']);
        })->name('browse');
    });

    // Volunteer dashboard (accessible for profile completion flow)
    Route::get('/volunteer/dashboard', [AuthController::class, 'volunteerDashboard'])->name('volunteer.dashboard');



    // Protected volunteer routes (require profile completion)
    Route::middleware('volunteer.profile.complete')->group(function () {

        // Volunteer opportunity browsing
        Route::get('/volunteer/opportunities', [\App\Http\Controllers\VolunteerOpportunityController::class, 'index'])->name('volunteer.opportunities.index');
        Route::get('/volunteer/opportunities/recommended', [\App\Http\Controllers\VolunteerOpportunityController::class, 'recommended'])->name('volunteer.opportunities.recommended');
        Route::get('/volunteer/opportunities/{opportunity}', [\App\Http\Controllers\VolunteerOpportunityController::class, 'show'])->name('volunteer.opportunities.show');
        Route::get('/volunteer/opportunities/{opportunity}/apply', [\App\Http\Controllers\VolunteerOpportunityController::class, 'apply'])->name('volunteer.opportunities.apply');

        // Volunteer applications
        Route::post('/volunteer/opportunities/{opportunity}/apply', [\App\Http\Controllers\ApplicationController::class, 'store'])->name('applications.store');
        Route::get('/volunteer/applications', [\App\Http\Controllers\ApplicationController::class, 'myApplications'])->name('volunteer.applications.index');
        Route::delete('/volunteer/applications/{application}/withdraw', [\App\Http\Controllers\ApplicationController::class, 'withdraw'])->name('applications.withdraw');

        // Volunteer assignments and calendar
        Route::get('/volunteer/assignments/{assignment}', [\App\Http\Controllers\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('/volunteer/assignments/{assignment}/accept', [\App\Http\Controllers\AssignmentController::class, 'accept'])->name('assignments.accept');
        Route::post('/volunteer/assignments/{assignment}/decline', [\App\Http\Controllers\AssignmentController::class, 'decline'])->name('assignments.decline');
        Route::post('/volunteer/assignments/{assignment}/check-in', [\App\Http\Controllers\AssignmentController::class, 'checkIn'])->name('assignments.check-in');
        Route::post('/volunteer/assignments/{assignment}/check-out', [\App\Http\Controllers\AssignmentController::class, 'checkOut'])->name('assignments.check-out');

        // Volunteer calendar
        Route::get('/volunteer/calendar', [\App\Http\Controllers\CalendarController::class, 'volunteerCalendar'])->name('volunteer.calendar');
        Route::get('/volunteer/calendar/events', [\App\Http\Controllers\CalendarController::class, 'volunteerCalendar'])->name('volunteer.calendar.events');

        // Volunteer settings
        Route::get('/volunteer/settings', [\App\Http\Controllers\VolunteerSettingsController::class, 'index'])->name('volunteer.settings');
        Route::post('/volunteer/settings', [\App\Http\Controllers\VolunteerSettingsController::class, 'update'])->name('volunteer.settings.update');

        // Volunteer notifications (shared routes - already defined in organization section)

        // Message routes (shared routes - already defined in organization section)

        // Announcement routes (shared routes - already defined in organization section)
    });
});
