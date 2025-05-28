<?php 

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\VolunteerSkillController;
use App\Http\Controllers\VolunteerProfileController;
use App\Http\Controllers\VolunteerDashboardController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\OrganizationDashboardController;
 use Illuminate\Support\Facades\Route;

 //authentification routes
 Route::post('/register', [AuthController::class, 'register']);
 Route::post('/login',[AuthController::class, 'login']);
 Route::post('/logout',[AuthController::class, 'logout'])->middleware('auth:sanctum');
 /*Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});*/

Route::middleware(['auth:sanctum', 'role:Volunteer'])
    ->get('/volunteer/dashboard', [VolunteerDashboardController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:Organization'])
    ->get('/organization/dashboard', [OrganizationDashboardController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:Admin'])
    ->get('/admin/dashboard', [AdminDashboardController::class, 'index']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

// Volunteer profile routes
Route::middleware(['auth:sanctum', 'role:Volunteer'])->group(function () {
    Route::get('/volunteer/profile', [VolunteerProfileController::class, 'show']);
    Route::post('/volunteer/profile', [VolunteerProfileController::class, 'storeOrUpdate']);
});

// Organization profile routes
Route::middleware(['auth:sanctum', 'role:Organization'])->group(function () {
    Route::get('/organization/profile', [OrganizationProfileController::class, 'show']);
    Route::post('/organization/profile', [OrganizationProfileController::class, 'storeOrUpdate']);
});

// Admin: Skill management
Route::middleware(['auth:sanctum', 'role:Admin'])->prefix('skills')->group(function () {
    Route::get('/', [SkillController::class, 'index']);
    Route::post('/', [SkillController::class, 'store']);
    Route::put('{skill}', [SkillController::class, 'update']);
    Route::delete('{skill}', [SkillController::class, 'destroy']);
});

// Volunteer: Attach skills
Route::middleware(['auth:sanctum', 'role:Volunteer'])->post('/volunteer/skills', [VolunteerSkillController::class, 'update']);

// Organization: Opportunity management
Route::middleware(['auth:sanctum', 'role:Organization'])->prefix('opportunities')->group(function () {
    Route::get('/', [OpportunityController::class, 'index']);
    Route::post('/', [OpportunityController::class, 'store']);
    Route::put('/{opportunity}', [OpportunityController::class, 'update']);
    Route::delete('/{opportunity}', [OpportunityController::class, 'destroy']);
});