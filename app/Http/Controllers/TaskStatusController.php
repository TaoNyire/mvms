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
            ->with(['volunteer', 'opportunity', 'taskStatus'])
            ->get();

        return response()->json($applications);
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
            ->with(['volunteer', 'opportunity', 'taskStatus'])
            ->orderByDesc('responded_at')
            ->limit(20)
            ->get();

        return response()->json($applications);
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