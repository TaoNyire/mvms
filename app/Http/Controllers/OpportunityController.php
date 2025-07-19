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

        // Check if user has organization profile
        if (!$user->organizationProfile || !$user->organizationProfile->is_complete) {
            return redirect()->back()->with('error', 'Please complete your organization profile before creating opportunities.');
        }

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
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'duration_hours' => 'nullable|integer|min:1|max:168',
            'application_deadline' => 'nullable|date|before:start_date|after:now',
            'volunteers_needed' => 'required|integer|min:1|max:100',
            'min_age' => 'nullable|integer|min:16|max:100',
            'max_age' => 'nullable|integer|min:16|max:100|gte:min_age',
            'required_skills' => 'nullable|array',
            'required_languages' => 'nullable|array',
            'is_paid' => 'in:0,1',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string',
            'provides_transport' => 'in:0,1',
            'provides_meals' => 'in:0,1',
            'provides_accommodation' => 'in:0,1',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'special_instructions' => 'nullable|string',
        ];

        // Custom validation messages
        $messages = [
            'application_deadline.before' => 'The application deadline must be before the opportunity start date.',
            'application_deadline.after' => 'The application deadline must be in the future.',
            'start_date.after_or_equal' => 'The start date must be today or in the future.',
            'end_date.after' => 'The end date must be after the start date.',
            'end_time.after' => 'The end time must be after the start time.',
            'max_age.gte' => 'The maximum age must be greater than or equal to the minimum age.',
            'is_paid.in' => 'The is paid field must be true or false.',
            'provides_transport.in' => 'The provides transport field must be true or false.',
            'provides_meals.in' => 'The provides meals field must be true or false.',
            'provides_accommodation.in' => 'The provides accommodation field must be true or false.',
        ];

        $validatedData = $request->validate($rules, $messages);

        // Set organization ID
        $validatedData['organization_id'] = $user->id;

        // Handle boolean fields - convert string values to proper booleans
        $validatedData['is_paid'] = (bool) $request->input('is_paid', false);
        $validatedData['provides_transport'] = (bool) $request->input('provides_transport', false);
        $validatedData['provides_meals'] = (bool) $request->input('provides_meals', false);
        $validatedData['provides_accommodation'] = (bool) $request->input('provides_accommodation', false);
        $validatedData['requires_background_check'] = $request->has('requires_background_check');
        $validatedData['requires_training'] = $request->has('requires_training');

        // Set default contact info if not provided
        if (empty($validatedData['contact_email'])) {
            $validatedData['contact_email'] = $user->email;
        }

        try {
            $opportunity = Opportunity::create($validatedData);

            if ($request->has('publish_now')) {
                $opportunity->publish();
                $message = "🎉 Opportunity '{$opportunity->title}' has been created and published successfully! Volunteers can now apply for this opportunity.";
            } else {
                $message = "✅ Opportunity '{$opportunity->title}' has been saved as draft. You can edit and publish it when ready.";
            }

            // Log successful creation
            \Log::info('Opportunity created successfully', [
                'opportunity_id' => $opportunity->id,
                'title' => $opportunity->title,
                'user_id' => $user->id,
                'status' => $opportunity->status
            ]);



            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'opportunity' => $opportunity
                ]);
            }

            return redirect()->route('opportunities.show', $opportunity)
                ->with('success', $message);

        } catch (\Illuminate\Database\QueryException $e) {
            // Database-specific errors
            \Log::error('Database error creating opportunity', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'sql_code' => $e->getCode()
            ]);

            $errorMessage = '❌ Database error occurred while saving the opportunity. Please check your data and try again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);

        } catch (\Exception $e) {
            // General errors
            \Log::error('Failed to create opportunity', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $errorMessage = '❌ An unexpected error occurred while creating the opportunity. Please try again or contact support if the problem persists.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
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
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'category' => 'required|string',
            'type' => 'required|in:one_time,recurring,ongoing',
            'urgency' => 'required|in:low,medium,high,urgent',
            'location_type' => 'required|in:physical,remote,hybrid',
            'address' => 'required_if:location_type,physical,hybrid|string',
            'district' => 'required_if:location_type,physical,hybrid|string',
            'region' => 'required_if:location_type,physical,hybrid|string',
            'start_date' => 'required|date',
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
            'is_paid' => 'in:0,1',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string',
            'provides_transport' => 'in:0,1',
            'provides_meals' => 'in:0,1',
            'provides_accommodation' => 'in:0,1',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'special_instructions' => 'nullable|string',
        ];

        // Custom validation messages
        $messages = [
            'application_deadline.before' => 'The application deadline must be before the opportunity start date.',
            'end_date.after' => 'The end date must be after the start date.',
            'end_time.after' => 'The end time must be after the start time.',
            'max_age.gte' => 'The maximum age must be greater than or equal to the minimum age.',
            'is_paid.in' => 'The is paid field must be true or false.',
            'provides_transport.in' => 'The provides transport field must be true or false.',
            'provides_meals.in' => 'The provides meals field must be true or false.',
            'provides_accommodation.in' => 'The provides accommodation field must be true or false.',
        ];

        $validatedData = $request->validate($rules, $messages);

        // Handle boolean fields - convert string values to proper booleans
        $validatedData['is_paid'] = (bool) $request->input('is_paid', false);
        $validatedData['provides_transport'] = (bool) $request->input('provides_transport', false);
        $validatedData['provides_meals'] = (bool) $request->input('provides_meals', false);
        $validatedData['provides_accommodation'] = (bool) $request->input('provides_accommodation', false);
        $validatedData['requires_background_check'] = $request->has('requires_background_check');
        $validatedData['requires_training'] = $request->has('requires_training');

        // Handle array fields
        if ($request->has('required_skills')) {
            $validatedData['required_skills'] = json_encode($request->input('required_skills'));
        }
        if ($request->has('required_languages')) {
            $validatedData['required_languages'] = json_encode($request->input('required_languages'));
        }

        // Set default contact info if not provided
        if (empty($validatedData['contact_email'])) {
            $validatedData['contact_email'] = Auth::user()->email;
        }

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
