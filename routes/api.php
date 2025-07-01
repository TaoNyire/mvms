<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\VolunteerSkillController;
use App\Http\Controllers\VolunteerProfileController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\VolunteerDashboardController;
use App\Http\Controllers\OpportunityMatchingController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\OrganizationDashboardController;


/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
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
Route::middleware(['auth:sanctum', 'role:Volunteer'])->group(function () {
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

    // Volunteer withdraws an application
    Route::delete('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw']);

    // Volunteer confirms or rejects accepted application
    Route::post('/applications/{application}/confirm', [ApplicationController::class, 'confirm']);
});

/*
|--------------------------------------------------------------------------
| Organization Routes (Requires Organization Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Organization'])->group(function () {
    // Dashboard
    Route::get('/organization/dashboard', [OrganizationDashboardController::class, 'index']);

    // Profile management
    Route::get('/organization/profile', [OrganizationProfileController::class, 'show']);
    Route::post('/organization/profile', [OrganizationProfileController::class, 'storeOrUpdate']);

    // Opportunity management
    Route::get('/opportunities', [OpportunityController::class, 'index']);
    Route::post('/opportunities/add', [OpportunityController::class, 'store']);
    Route::put('/opportunities/{opportunity}', [OpportunityController::class, 'update']);
    Route::delete('/opportunities/{opportunity}', [OpportunityController::class, 'destroy']);

    // Organization views and responds to applications
    Route::get('/applications', [ApplicationController::class, 'orgApplications']);
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
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Requires Admin Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

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
});

/*
|--------------------------------------------------------------------------
| Public Routes (Accessible to Anyone)
|--------------------------------------------------------------------------
*/

// List/filter public opportunities
Route::get('/opportunities/public', [OpportunityMatchingController::class, 'publicIndex']);