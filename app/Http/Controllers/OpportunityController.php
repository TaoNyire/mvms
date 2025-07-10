<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opportunity;

class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return $user->opportunities()->with('skills')->get();
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find the opportunity with relationships
            $opportunity = Opportunity::with(['skills', 'organization'])
                ->withCount('applications')
                ->findOrFail($id);

            // Check if user has permission to view this opportunity
            // Organizations can only view their own opportunities
            if ($user->hasRole('organization') && $opportunity->organization_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'data' => $opportunity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Opportunity not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'volunteers_needed' => 'required|integer|min:1',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);
        $data['organization_id'] = $request->user()->id;
        $opportunity = Opportunity::create($data);
        if (!empty($data['skills'])) {
            $opportunity->skills()->sync($data['skills']);
        }
        return response()->json($opportunity->load('skills'), 201);
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        // Ensure that the opportunity belongs to this org
        if ($opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'volunteers_needed' => 'sometimes|required|integer|min:1',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);
        $opportunity->update($data);
        if (isset($data['skills'])) {
            $opportunity->skills()->sync($data['skills']);
        }
        return response()->json($opportunity->load('skills'));
    }

    public function destroy(Request $request, Opportunity $opportunity)
    {
        if ($opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $opportunity->delete();
        return response()->json(['message' => 'Opportunity deleted']);
    }

    /**
     * Get applications for a specific opportunity
     */
    public function getApplications(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find the opportunity
            $opportunity = Opportunity::findOrFail($id);

            // Check if user has permission to view applications for this opportunity
            if ($user->hasRole('organization') && $opportunity->organization_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get applications with volunteer details
            $applications = $opportunity->applications()
                ->with(['volunteer', 'volunteer.volunteerProfile'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($application) {
                    return [
                        'id' => $application->id,
                        'status' => $application->status,
                        'applied_at' => $application->applied_at,
                        'responded_at' => $application->responded_at,
                        'feedback_rating' => $application->feedback_rating,
                        'feedback_comment' => $application->feedback_comment,
                        'volunteer' => [
                            'id' => $application->volunteer->id,
                            'name' => $application->volunteer->name,
                            'email' => $application->volunteer->email,
                            'profile' => $application->volunteer->volunteerProfile
                        ]
                    ];
                });

            return response()->json([
                'data' => $applications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get applications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all opportunities with pagination and filtering
     */
    public function adminIndex(Request $request)
    {
        $query = Opportunity::with(['organization', 'skills'])
            ->withCount('applications')
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $opportunities = $query->paginate(20);
        return response()->json($opportunities);
    }
}