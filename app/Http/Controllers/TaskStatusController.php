<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\TaskStatus;
use App\Models\Opportunity;

class TaskStatusController extends Controller
{
    /**
     * Organization/Admin: Set task status (completed, quit)
     * status must be 'completed' or 'quit'
     */
    public function setStatus(Request $request, $application_id)
    {
        $user = $request->user();
        $application = Application::with('opportunity', 'taskStatus')->findOrFail($application_id);

        // Only org that owns the opportunity or admin can set status
        if (
            !$user->hasRole('admin') &&
            $application->opportunity->organization_id !== $user->id
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:completed,quit',
            'remarks' => 'nullable|string'
        ]);

        // Only allow update if current status is in_progress and not already completed/quit
        if (!$application->taskStatus || !in_array($application->taskStatus->status, ['in_progress'])) {
            return response()->json(['message' => 'Task is not in progress or already finalized'], 400);
        }

        $application->taskStatus->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'updated_at' => now()
        ]);

        return response()->json($application->taskStatus->fresh());
    }

    /**
     * Anyone involved (org, volunteer, admin) can view task status for an application
     */
    public function applicationStatus(Request $request, $application_id)
    {
        $application = Application::with(['taskStatus', 'volunteer', 'opportunity'])->findOrFail($application_id);
        $user = $request->user();

        $isOrg = $application->opportunity->organization_id === $user->id;
        $isVolunteer = $application->volunteer_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (!($isOrg || $isVolunteer || $isAdmin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($application->taskStatus);
    }

    /**
     * Organization: Get currently working volunteers (status=in_progress)
     */
    public function currentVolunteers(Request $request)
    {
        $org = $request->user();

        $applications = Application::whereHas('opportunity', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })
            ->whereHas('taskStatus', function ($q) {
                $q->where('status', 'in_progress');
            })
            ->with(['volunteer.volunteerProfile.skills', 'opportunity.skills', 'taskStatus'])
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
     * Organization: Get recently assigned volunteers (recently responded)
     */
    public function recentVolunteers(Request $request)
    {
        $org = $request->user();
        $recentWindow = now()->subDays(30);

        $applications = Application::whereHas('opportunity', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })
            ->where('status', 'accepted')
            ->where('responded_at', '>=', $recentWindow)
            ->with(['volunteer.volunteerProfile.skills', 'opportunity.skills', 'taskStatus'])
            ->orderByDesc('responded_at')
            ->limit(20)
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
     * Organization: List all volunteers & their task statuses for a given opportunity
     */
    public function volunteerTasks(Request $request, $opportunity_id)
    {
        $org = $request->user();
        $opportunity = Opportunity::where('id', $opportunity_id)
            ->where('organization_id', $org->id)
            ->firstOrFail();

        $applications = $opportunity->applications()
            ->with(['volunteer', 'taskStatus'])
            ->get();

        return response()->json($applications);
    }
}