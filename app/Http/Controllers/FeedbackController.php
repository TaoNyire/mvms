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

    /**
     * Admin: Get all feedback with pagination and filtering
     */
    public function adminIndex(Request $request)
    {
        $query = Feedback::with([
            'application.volunteer',
            'application.opportunity',
            'fromUser'
        ])->orderBy('created_at', 'desc');

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        if ($request->search) {
            $query->whereHas('application.volunteer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhereHas('application.opportunity', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            });
        }

        $feedbacks = $query->paginate(20);
        return response()->json($feedbacks);
    }

    /**
     * Get feedback status for a specific application
     */
    public function getFeedbackStatus(Request $request, $application_id)
    {
        $user = $request->user();
        $application = Application::with(['taskStatus', 'feedback', 'opportunity', 'volunteer'])->findOrFail($application_id);

        // Check if user has access to this application
        $isOrganization = $application->opportunity->organization_id === $user->id;
        $isVolunteer = $application->volunteer_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (!($isOrganization || $isVolunteer || $isAdmin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if feedback is allowed
        $feedbackAllowed = $application->status === 'accepted' &&
                          $application->taskStatus &&
                          in_array($application->taskStatus->status, ['completed', 'quit']);

        // Get existing feedback
        $orgFeedback = Feedback::where('application_id', $application_id)
                              ->where('from_type', 'org')
                              ->with('fromUser')
                              ->first();

        $volunteerFeedback = Feedback::where('application_id', $application_id)
                                    ->where('from_type', 'volunteer')
                                    ->with('fromUser')
                                    ->first();

        return response()->json([
            'feedback_allowed' => $feedbackAllowed,
            'application_status' => $application->status,
            'task_status' => $application->taskStatus?->status,
            'org_feedback' => $orgFeedback,
            'volunteer_feedback' => $volunteerFeedback,
            'can_submit_org_feedback' => $isOrganization && $feedbackAllowed && !$orgFeedback,
            'can_submit_volunteer_feedback' => $isVolunteer && $feedbackAllowed && !$volunteerFeedback,
            'can_view_org_feedback' => $isVolunteer || $isAdmin || $isOrganization,
            'can_view_volunteer_feedback' => $isOrganization || $isAdmin || $isVolunteer,
        ]);
    }
}