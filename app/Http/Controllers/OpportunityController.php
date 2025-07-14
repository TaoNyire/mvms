<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Opportunity;
use App\Models\Application;

class OpportunityController extends Controller
{
    /**
     * Display a listing of opportunities for organization
     */
    public function index()
    {
        $user = Auth::user();

        $opportunities = Opportunity::where('organization_id', $user->id)
            ->with(['applications' => function($query) {
                $query->with('volunteer');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('organization.opportunities.index', compact('opportunities'));
    }

    /**
     * Show the form for creating a new opportunity
     */
    public function create()
    {
        return view('organization.opportunities.create');
    }

    /**
     * Store a newly created opportunity
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'category' => 'required|string',
            'type' => 'required|in:one_time,recurring,ongoing',
            'urgency' => 'required|in:low,medium,high,urgent',
            'location_type' => 'required|in:physical,remote,hybrid',
            'address' => 'required_if:location_type,physical,hybrid|string',
            'district' => 'required_if:location_type,physical,hybrid|string',
            'region' => 'required_if:location_type,physical,hybrid|string',
            'start_date' => 'required|date|after:today',
            'end_date' => 'nullable|date|after:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'duration_hours' => 'nullable|integer|min:1|max:168',
            'application_deadline' => 'nullable|date|before:start_date',
            'volunteers_needed' => 'required|integer|min:1|max:100',
            'min_age' => 'nullable|integer|min:16|max:100',
            'max_age' => 'nullable|integer|min:16|max:100|gte:min_age',
            'required_skills' => 'nullable|array',
            'required_languages' => 'nullable|array',
            'is_paid' => 'boolean',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string',
            'provides_transport' => 'boolean',
            'provides_meals' => 'boolean',
            'provides_accommodation' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'special_instructions' => 'nullable|string',
        ];

        $validatedData = $request->validate($rules);

        // Set organization ID
        $validatedData['organization_id'] = $user->id;

        // Handle boolean fields
        $validatedData['is_paid'] = $request->has('is_paid');
        $validatedData['provides_transport'] = $request->has('provides_transport');
        $validatedData['provides_meals'] = $request->has('provides_meals');
        $validatedData['provides_accommodation'] = $request->has('provides_accommodation');
        $validatedData['requires_background_check'] = $request->has('requires_background_check');
        $validatedData['requires_training'] = $request->has('requires_training');

        // Set default contact info if not provided
        if (empty($validatedData['contact_email'])) {
            $validatedData['contact_email'] = $user->email;
        }

        $opportunity = Opportunity::create($validatedData);

        if ($request->has('publish_now')) {
            $opportunity->publish();
            $message = 'Opportunity created and published successfully!';
        } else {
            $message = 'Opportunity created as draft. You can publish it later.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'opportunity' => $opportunity
            ]);
        }

        return redirect()->route('opportunities.show', $opportunity)
            ->with('success', $message);
    }

    /**
     * Display the specified opportunity
     */
    public function show(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $opportunity->load(['applications.volunteer.volunteerProfile']);

        // Increment views count
        $opportunity->incrementViews();

        return view('organization.opportunities.show', compact('opportunity'));
    }

    /**
     * Show the form for editing the opportunity
     */
    public function edit(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        return view('organization.opportunities.edit', compact('opportunity'));
    }

    /**
     * Update the specified opportunity
     */
    public function update(Request $request, Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'category' => 'required|string',
            'start_date' => 'required|date',
            'volunteers_needed' => 'required|integer|min:1|max:100',
            // Add other validation rules as needed
        ];

        $validatedData = $request->validate($rules);

        $opportunity->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Opportunity updated successfully!',
                'opportunity' => $opportunity
            ]);
        }

        return redirect()->route('opportunities.show', $opportunity)
            ->with('success', 'Opportunity updated successfully!');
    }

    /**
     * Remove the specified opportunity
     */
    public function destroy(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if opportunity has accepted applications
        if ($opportunity->acceptedApplications()->count() > 0) {
            return back()->with('error', 'Cannot delete opportunity with accepted applications.');
        }

        $opportunity->delete();

        return redirect()->route('opportunities.index')
            ->with('success', 'Opportunity deleted successfully!');
    }

    /**
     * Publish an opportunity
     */
    public function publish(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $opportunity->publish();

        return back()->with('success', 'Opportunity published successfully!');
    }

    /**
     * Pause an opportunity
     */
    public function pause(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $opportunity->update(['status' => 'paused']);

        return back()->with('success', 'Opportunity paused successfully!');
    }

    /**
     * Complete an opportunity
     */
    public function complete(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $opportunity->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return back()->with('success', 'Opportunity marked as completed!');
    }
}
