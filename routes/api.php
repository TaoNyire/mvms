<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\VolunteerSkillController;
use App\Http\Controllers\VolunteerProfileController;
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
});

/*
|--------------------------------------------------------------------------
| Public Routes (Accessible to Anyone)
|--------------------------------------------------------------------------
*/

// List/filter public opportunities
Route::get('/opportunities/public', [OpportunityMatchingController::class, 'publicIndex']);
