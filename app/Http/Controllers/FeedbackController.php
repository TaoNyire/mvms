<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Application;

class FeedbackController extends Controller
{
    /**
     * Organization submits feedback for a volunteer on an application.
     * One feedback per application per org.
     */
    public function submitByOrg(Request $request, $application_id)
    {
        $user = $request->user();
        $application = Application::with('opportunity', 'volunteer', 'feedback')->findOrFail($application_id);

        // Only the org that owns the opportunity can give feedback
        if ($application->opportunity->organization_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Only allowed if application is accepted and taskStatus is completed or quit
        if (
            $application->status !== 'accepted' ||
            !$application->taskStatus ||
            !in_array($application->taskStatus->status, ['completed', 'quit'])
        ) {
            return response()->json(['message' => 'Feedback only allowed after completion/quit'], 400);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string'
        ]);

        $feedback = Feedback::updateOrCreate(
            [
                'application_id' => $application->id,
                'from_type' => 'org',
                'from_user_id' => $user->id
            ],
            $data
        );

        return response()->json(['message' => 'Feedback submitted', 'feedback' => $feedback]);
    }

    /**
     * Volunteer submits feedback for org on an application.
     * One feedback per application per volunteer.
     */
    public function submitByVolunteer(Request $request, $application_id)
    {
        $user = $request->user();
        $application = Application::with('opportunity', 'volunteer', 'feedback')->findOrFail($application_id);

        // Only the volunteer assigned to this application
        if ($application->volunteer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Only if application is accepted and taskStatus is completed or quit
        if (
            $application->status !== 'accepted' ||
            !$application->taskStatus ||
            !in_array($application->taskStatus->status, ['completed','quit'])
        ) {
            return response()->json(['message' => 'Feedback only allowed after completion/quit'], 400);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string'
        ]);

        $feedback = Feedback::updateOrCreate(
            [
                'application_id' => $application->id,
                'from_type' => 'volunteer',
                'from_user_id' => $user->id,
            ],
            $data
        );

        return response()->json(['message' => 'Feedback submitted', 'feedback' => $feedback]);
    }

    /**
     * Volunteer views all feedback received (from orgs) on their applications.
     */
    public function myFeedback(Request $request)
    {
        $user = $request->user();

        $feedbacks = Feedback::where('from_type', 'org')
            ->whereHas('application', function ($q) use ($user) {
                $q->where('volunteer_id', $user->id);
            })
            ->with(['application.opportunity', 'fromUser'])
            ->latest()
            ->get();

        return response()->json($feedbacks);
    }

    /**
     * Organization views all feedback received from volunteers on their opportunities.
     */
    public function orgFeedbackHistory(Request $request)
    {
        $user = $request->user();

        $feedbacks = Feedback::where('from_type', 'volunteer')
            ->whereHas('application.opportunity', function ($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->with(['application.volunteer', 'application.opportunity', 'fromUser'])
            ->latest()
            ->get();

        return response()->json($feedbacks);
    }
}