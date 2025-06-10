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
     * Prevents duplicate applications
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

        $application = Application::create([
            'volunteer_id' => $user->id,
            'opportunity_id' => $opportunity_id,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        return response()->json($application, 201);
    }

    /**
     * Organization views all applications to their opportunities
     */
    public function orgApplications(Request $request)
    {
        $user = $request->user();
        // Only applications for opportunities owned by this org user
        $applications = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->with(['volunteer', 'opportunity'])
            ->latest('applied_at')
            ->paginate(20);

        return response()->json($applications);
    }

    /**
     * Organization accepts or rejects an application (update status + responded_at)
     * Triggers notification to volunteer
     */
    public function respond(Request $request, $application_id)
    {
        $request->validate(['status' => 'required|in:accepted,rejected']);

        $orgUser = $request->user();
        $application = Application::with('opportunity')->findOrFail($application_id);

        // Only allow if this org owns the opportunity
        if ($application->opportunity->organization_id !== $orgUser->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application->status = $request->status;
        $application->responded_at = now();
        $application->save();

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
            ->with(['opportunity'])
            ->latest('applied_at')
            ->paginate(20);

        return response()->json($applications);
    }
}