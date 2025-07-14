<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\Opportunity;

class ApplicationController extends Controller
{
    /**
     * Apply for an opportunity (Volunteer)
     */
    public function store(Request $request, Opportunity $opportunity)
    {
        $user = Auth::user();

        // Check if opportunity is still accepting applications
        if (!$opportunity->is_active) {
            return back()->with('error', 'This opportunity is no longer accepting applications.');
        }

        // Check if opportunity is full
        if ($opportunity->is_full) {
            return back()->with('error', 'This opportunity is already full.');
        }

        // Check if user already applied
        $existingApplication = Application::where('opportunity_id', $opportunity->id)
            ->where('volunteer_id', $user->id)
            ->first();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied for this opportunity.');
        }

        $rules = [
            'message' => 'required|string|min:50|max:1000',
            'relevant_experience' => 'nullable|string|max:1000',
            'availability_details' => 'nullable|string|max:500',
            'agrees_to_terms' => 'required|accepted',
        ];

        $validatedData = $request->validate($rules);

        $application = Application::create([
            'opportunity_id' => $opportunity->id,
            'volunteer_id' => $user->id,
            'message' => $validatedData['message'],
            'relevant_experience' => $validatedData['relevant_experience'],
            'availability_details' => $validatedData['availability_details'] ? [$validatedData['availability_details']] : null,
            'agrees_to_terms' => true,
            'applied_at' => now(),
        ]);

        // Increment applications count
        $opportunity->increment('applications_count');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'application' => $application
            ]);
        }

        return back()->with('success', 'Your application has been submitted successfully! The organization will review it and get back to you.');
    }

    /**
     * Show application details
     */
    public function show(Application $application)
    {
        $user = Auth::user();

        // Check if user can view this application
        if ($application->volunteer_id !== $user->id &&
            $application->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this application.');
        }

        $application->load(['opportunity', 'volunteer.volunteerProfile']);

        return view('applications.show', compact('application'));
    }

    /**
     * Accept an application (Organization)
     */
    public function accept(Request $request, Application $application)
    {
        $user = Auth::user();

        // Check if user owns the opportunity
        if ($application->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this application.');
        }

        // Check if opportunity is full
        if ($application->opportunity->is_full) {
            return back()->with('error', 'This opportunity is already full.');
        }

        $notes = $request->input('notes');
        $application->accept($notes);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Application accepted successfully!',
                'application' => $application
            ]);
        }

        return back()->with('success', 'Application accepted! The volunteer has been notified.');
    }

    /**
     * Reject an application (Organization)
     */
    public function reject(Request $request, Application $application)
    {
        $user = Auth::user();

        // Check if user owns the opportunity
        if ($application->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this application.');
        }

        $reason = $request->input('reason');
        $application->reject($reason);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Application rejected.',
                'application' => $application
            ]);
        }

        return back()->with('success', 'Application rejected. The volunteer has been notified.');
    }

    /**
     * Withdraw an application (Volunteer)
     */
    public function withdraw(Application $application)
    {
        $user = Auth::user();

        // Check if user owns this application
        if ($application->volunteer_id !== $user->id) {
            abort(403, 'Unauthorized access to this application.');
        }

        try {
            $application->withdraw();
            return back()->with('success', 'Application withdrawn successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * List applications for volunteer
     */
    public function myApplications()
    {
        $user = Auth::user();

        $applications = Application::where('volunteer_id', $user->id)
            ->with(['opportunity.organization'])
            ->orderBy('applied_at', 'desc')
            ->paginate(10);

        return view('volunteer.applications.index', compact('applications'));
    }

    /**
     * List applications for organization's opportunities
     */
    public function organizationApplications()
    {
        $user = Auth::user();

        $applications = Application::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->with(['opportunity', 'volunteer.volunteerProfile'])
            ->orderBy('applied_at', 'desc')
            ->paginate(15);

        return view('organization.applications.index', compact('applications'));
    }

    /**
     * Bulk actions for applications (Organization)
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'action' => 'required|in:accept,reject',
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ];

        $validatedData = $request->validate($rules);

        $applications = Application::whereIn('id', $validatedData['application_ids'])
            ->whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->get();

        $successCount = 0;

        foreach ($applications as $application) {
            try {
                if ($validatedData['action'] === 'accept') {
                    if (!$application->opportunity->is_full) {
                        $application->accept($validatedData['notes'] ?? null);
                        $successCount++;
                    }
                } else {
                    $application->reject($validatedData['reason'] ?? null);
                    $successCount++;
                }
            } catch (\Exception $e) {
                \Log::error("Failed to {$validatedData['action']} application {$application->id}: " . $e->getMessage());
            }
        }

        $action = $validatedData['action'] === 'accept' ? 'accepted' : 'rejected';
        return back()->with('success', "{$successCount} applications {$action} successfully.");
    }
}
