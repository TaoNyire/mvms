<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ApplicationStatusNotification;

class ApplicationController extends Controller
{
    /**
     * Volunteer applies to an opportunity
     * - Prevents duplicate applications
     * - Checks volunteer application limit if already recruited
     * - Checks if opportunity is still hiring
     * - Checks if opportunity is already in progress or completed
     * - Notifies organization of new application (with volunteer profile, CV & qualifications)
     */
    public function apply(Request $request, $opportunity_id)
    {
        $user = $request->user();

        // Check if already applied
        $alreadyApplied = Application::where('volunteer_id', $user->id)
            ->where('opportunity_id', $opportunity_id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'Already applied to this opportunity'], 409);
        }

        // Check if opportunity exists and is still hiring
        $opportunity = Opportunity::findOrFail($opportunity_id);

        // Exclude opportunities that are in progress or completed
        if (in_array($opportunity->status, ['in_progress', 'completed'])) {
            return response()->json(['message' => 'This opportunity is no longer accepting applications.'], 403);
        }

        // Ensure not over-hired
        $acceptedCount = Application::where('opportunity_id', $opportunity_id)
            ->where('status', 'accepted')
            ->count();
        if ($acceptedCount >= $opportunity->volunteers_needed) {
            return response()->json(['message' => 'This opportunity has reached the required number of volunteers.'], 403);
        }

        // Check if volunteer is already recruited (accepted) elsewhere
        $recruited = Application::where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('opportunity', function($q) {
                $q->whereIn('status', ['in_progress']);
            })
            ->count();

        // If working on another org's in-progress opportunity, only 2 applications allowed
        if ($recruited) {
            $pendingAppsCount = Application::where('volunteer_id', $user->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->whereHas('opportunity', function($q) {
                    $q->whereIn('status', ['pending', 'in_progress']);
                })
                ->count();

            if ($pendingAppsCount >= 2) {
                return response()->json(['message' => 'You can only apply to 2 opportunities while working on another organization\'s task.'], 403);
            }
        }

        $application = Application::create([
            'volunteer_id' => $user->id,
            'opportunity_id' => $opportunity_id,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        // Notify organization of new application
        $organization = $opportunity->organization; // assumes $opportunity->organization returns User model
        if ($organization) {
            // You might want to create a new notification for new applications, but for now using ApplicationStatusNotification
            $organization->notify(new ApplicationStatusNotification($application));
        }

        return response()->json($application->load('volunteer.volunteerProfile'), 201);
    }

    /**
     * Organization views all applications to their opportunities
     */
    public function orgApplications(Request $request)
    {
        $user = $request->user();
        // Eager load volunteer's profile (with CV & qualifications)
        $applications = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->with(['volunteer.volunteerProfile.skills', 'opportunity.skills', 'taskStatus'])
            ->latest('applied_at')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'status' => $application->status,
                    'applied_at' => $application->applied_at,
                    'responded_at' => $application->responded_at,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at,

                    // Volunteer information
                    'volunteer_id' => $application->volunteer_id,
                    'volunteer_name' => $application->volunteer->name ?? 'Unknown Volunteer',
                    'volunteer_email' => $application->volunteer->email ?? 'No email',
                    'volunteer_phone' => $application->volunteer->volunteerProfile->phone ?? null,
                    'volunteer_location' => $application->volunteer->volunteerProfile->location ?? null,
                    'volunteer_bio' => $application->volunteer->volunteerProfile->bio ?? null,
                    'volunteer_skills' => $application->volunteer->volunteerProfile ?
                        $application->volunteer->volunteerProfile->skills->pluck('name')->toArray() : [],

                    // Opportunity information
                    'opportunity_id' => $application->opportunity_id,
                    'opportunity_title' => $application->opportunity->title ?? 'Unknown Opportunity',
                    'opportunity_description' => $application->opportunity->description ?? null,
                    'opportunity_location' => $application->opportunity->location ?? null,
                    'opportunity_start_date' => $application->opportunity->start_date ?? null,
                    'opportunity_end_date' => $application->opportunity->end_date ?? null,
                    'opportunity_volunteers_needed' => $application->opportunity->volunteers_needed ?? null,
                    'opportunity_skills' => $application->opportunity->skills->pluck('name')->toArray() ?? [],

                    // Task status
                    'task_status' => $application->taskStatus ? [
                        'status' => $application->taskStatus->status,
                        'started_at' => $application->taskStatus->started_at,
                        'completed_at' => $application->taskStatus->completed_at,
                    ] : null,

                    // Relationships for frontend compatibility
                    'volunteer' => $application->volunteer,
                    'opportunity' => $application->opportunity,
                ];
            });

        return response()->json([
            'data' => $applications,
            'total' => $applications->count(),
        ]);
    }

    /**
     * Show a single application (for organization)
     */
    public function show(Request $request, $application_id)
    {
        $user = $request->user();
        $application = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->with(['volunteer.volunteerProfile.skills', 'opportunity.skills', 'taskStatus'])
            ->findOrFail($application_id);

        return response()->json([
            'id' => $application->id,
            'status' => $application->status,
            'applied_at' => $application->applied_at,
            'responded_at' => $application->responded_at,
            'created_at' => $application->created_at,
            'updated_at' => $application->updated_at,

            // Volunteer information
            'volunteer_id' => $application->volunteer_id,
            'volunteer_name' => $application->volunteer->name ?? 'Unknown Volunteer',
            'volunteer_email' => $application->volunteer->email ?? 'No email',
            'volunteer_phone' => $application->volunteer->volunteerProfile->phone ?? null,
            'volunteer_location' => $application->volunteer->volunteerProfile->location ?? null,
            'volunteer_bio' => $application->volunteer->volunteerProfile->bio ?? null,
            'volunteer_skills' => $application->volunteer->volunteerProfile ?
                $application->volunteer->volunteerProfile->skills->pluck('name')->toArray() : [],

            // Opportunity information
            'opportunity_id' => $application->opportunity_id,
            'opportunity_title' => $application->opportunity->title ?? 'Unknown Opportunity',
            'opportunity_description' => $application->opportunity->description ?? null,
            'opportunity_location' => $application->opportunity->location ?? null,
            'opportunity_start_date' => $application->opportunity->start_date ?? null,
            'opportunity_end_date' => $application->opportunity->end_date ?? null,
            'opportunity_volunteers_needed' => $application->opportunity->volunteers_needed ?? null,
            'opportunity_skills' => $application->opportunity->skills->pluck('name')->toArray() ?? [],

            // Task status
            'task_status' => $application->taskStatus ? [
                'status' => $application->taskStatus->status,
                'started_at' => $application->taskStatus->started_at,
                'completed_at' => $application->taskStatus->completed_at,
            ] : null,

            // Relationships for frontend compatibility
            'volunteer' => $application->volunteer,
            'opportunity' => $application->opportunity,
        ]);
    }

    /**
     * Organization accepts or rejects an application (update status + responded_at)
     * Triggers notification to volunteer
     * If required number of volunteers reached, set opportunity status to in_progress
     */
    public function respond(Request $request, $application_id)
    {
        $request->validate(['status' => 'required|in:accepted,rejected']);

        $orgUser = $request->user();
        $application = Application::with(['opportunity', 'volunteer.volunteerProfile'])->findOrFail($application_id);

        // Only allow if this org owns the opportunity
        if ($application->opportunity->organization_id !== $orgUser->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If accepting, ensure not over-hired
        if ($request->status === 'accepted') {
            $acceptedCount = Application::where('opportunity_id', $application->opportunity_id)
                ->where('status', 'accepted')
                ->count();
            if ($acceptedCount >= $application->opportunity->volunteers_needed) {
                return response()->json(['message' => 'This opportunity has already filled all volunteer positions.'], 403);
            }
        }

        $application->status = $request->status;
        $application->responded_at = now();
        $application->save();

        // If accepted, check if we need to update opportunity status
        if ($request->status === 'accepted') {
            $acceptedCount = Application::where('opportunity_id', $application->opportunity_id)
                ->where('status', 'accepted')
                ->count();
            $opportunity = $application->opportunity;
            if ($acceptedCount >= $opportunity->volunteers_needed && $opportunity->status !== 'completed') {
                $opportunity->status = 'in_progress';
                $opportunity->save();
            }
        }

        // Notify volunteer (email and database)
        $application->volunteer->notify(new ApplicationStatusNotification($application));

        return response()->json($application);
    }

    /**
     * Volunteer views all their applications
     */
    public function myApplications(Request $request)
    {
        $user = $request->user();
        $applications = Application::where('volunteer_id', $user->id)
            ->with(['opportunity.organization', 'opportunity.skills'])
            ->latest('applied_at')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'status' => $application->status,
                    'applied_at' => $application->applied_at,
                    'responded_at' => $application->responded_at,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at,

                    // Opportunity information
                    'opportunity' => [
                        'id' => $application->opportunity->id,
                        'title' => $application->opportunity->title,
                        'description' => $application->opportunity->description,
                        'location' => $application->opportunity->location,
                        'start_date' => $application->opportunity->start_date,
                        'end_date' => $application->opportunity->end_date,
                        'volunteers_needed' => $application->opportunity->volunteers_needed,
                        'status' => $application->opportunity->status ?? 'active',
                        'skills' => $application->opportunity->skills->pluck('name')->toArray(),

                        // Organization information
                        'organization' => [
                            'id' => $application->opportunity->organization->id ?? null,
                            'name' => $application->opportunity->organization->name ?? 'Unknown Organization',
                            'email' => $application->opportunity->organization->email ?? null,
                        ]
                    ]
                ];
            });

        return response()->json([
            'data' => $applications,
            'total' => $applications->count()
        ]);
    }

    /**
     * Volunteer withdraws an application
     * - Only if status is pending
     */
    public function withdraw(Request $request, $application_id)
    {
        $application = Application::findOrFail($application_id);

        // Only allow withdrawal if application is pending
        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Cannot withdraw this application'], 403);
        }

        $application->delete();

        return response()->json(['message' => 'Application withdrawn successfully']);
    }

    /**
     * Volunteer confirms or rejects accepted application
     */
    public function confirm(Request $request, $application_id)
    {
        $user = $request->user();
        $request->validate(['confirmation_status' => 'required|in:confirmed,rejected']);

        $application = Application::where('id', $application_id)
            ->where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->where('confirmation_status', 'pending')
            ->firstOrFail();

        $application->confirmation_status = $request->confirmation_status;
        $application->confirmed_at = now();
        if ($request->confirmation_status === 'rejected') {
            // Free up the slot
            $application->status = 'rejected';
        }
        $application->save();

        return response()->json($application);
    }

    /**
     * Admin: Get all applications with pagination and filtering
     */
    public function adminIndex(Request $request)
    {
        $query = Application::with([
                'volunteer.volunteerProfile.skills',
                'opportunity.organization.organizationProfile',
                'opportunity.skills',
                'taskStatus'
            ])
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('volunteer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            })->orWhereHas('opportunity', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            })->orWhereHas('opportunity.organization', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $applications = $query->get()->map(function ($application) {
            return [
                'id' => $application->id,
                'status' => $application->status,
                'applied_at' => $application->applied_at,
                'responded_at' => $application->responded_at,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,

                // Volunteer information
                'volunteer_id' => $application->volunteer_id,
                'volunteer_name' => $application->volunteer->name ?? 'Unknown Volunteer',
                'volunteer_email' => $application->volunteer->email ?? 'No email',
                'volunteer_phone' => $application->volunteer->volunteerProfile->phone ?? null,
                'volunteer_location' => $application->volunteer->volunteerProfile->location ?? null,
                'volunteer_bio' => $application->volunteer->volunteerProfile->bio ?? null,
                'volunteer_skills' => $application->volunteer->volunteerProfile ?
                    $application->volunteer->volunteerProfile->skills->pluck('name')->toArray() : [],

                // Opportunity information
                'opportunity_id' => $application->opportunity_id,
                'opportunity_title' => $application->opportunity->title ?? 'Unknown Opportunity',
                'opportunity_description' => $application->opportunity->description ?? null,
                'opportunity_location' => $application->opportunity->location ?? null,
                'opportunity_start_date' => $application->opportunity->start_date ?? null,
                'opportunity_end_date' => $application->opportunity->end_date ?? null,
                'opportunity_volunteers_needed' => $application->opportunity->volunteers_needed ?? null,
                'opportunity_skills' => $application->opportunity->skills->pluck('name')->toArray() ?? [],

                // Organization information
                'organization_id' => $application->opportunity->organization_id ?? null,
                'organization_name' => $application->opportunity->organization->name ?? 'Unknown Organization',
                'organization_email' => $application->opportunity->organization->email ?? null,
                'organization_profile_name' => $application->opportunity->organization->organizationProfile->org_name ?? null,

                // Task status
                'task_status' => $application->taskStatus ? [
                    'status' => $application->taskStatus->status,
                    'started_at' => $application->taskStatus->started_at,
                    'completed_at' => $application->taskStatus->completed_at,
                ] : null,

                // Relationships for frontend compatibility
                'volunteer' => $application->volunteer,
                'opportunity' => $application->opportunity,
            ];
        });

        return response()->json([
            'data' => $applications,
            'total' => $applications->count(),
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $applications->count(),
        ]);
    }

    /**
     * Admin: Update application status
     */
    public function updateStatus(Request $request, Application $application)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,accepted,rejected,completed'
        ]);

        $application->update($data);

        return response()->json([
            'message' => 'Application status updated successfully',
            'application' => $application->load(['volunteer', 'opportunity'])
        ]);
    }
}