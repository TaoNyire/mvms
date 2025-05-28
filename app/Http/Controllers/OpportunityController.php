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
}