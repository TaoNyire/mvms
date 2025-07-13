<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skill;
use Illuminate\Validation\Rule;

class OrganizationSkillController extends Controller
{
    /**
     * Get all skills available to the organization (global + organization-specific)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $skills = Skill::forOrganization($user->id)
                      ->active()
                      ->orderBy('skill_type')
                      ->orderBy('priority', 'desc')
                      ->orderBy('name')
                      ->get();

        return response()->json([
            'skills' => $skills,
            'global_skills' => $skills->where('skill_type', 'global')->values(),
            'organization_skills' => $skills->where('skill_type', 'organization_specific')->values(),
        ]);
    }

    /**
     * Get only organization-specific skills
     */
    public function organizationSkills(Request $request)
    {
        $user = $request->user();

        $skills = Skill::organizationSpecific($user->id)
                      ->active()
                      ->orderBy('priority', 'desc')
                      ->orderBy('name')
                      ->get();

        return response()->json($skills);
    }

    /**
     * Create a new organization-specific skill
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('skills')->where(function ($query) use ($user) {
                    return $query->where('organization_id', $user->id);
                })
            ],
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'required_proficiency_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);

        $skill = Skill::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'required_proficiency_level' => $data['required_proficiency_level'] ?? 'intermediate',
            'priority' => $data['priority'] ?? 0,
            'organization_id' => $user->id,
            'skill_type' => 'organization_specific',
            'is_active' => true,
        ]);

        return response()->json($skill, 201);
    }

    /**
     * Update an organization-specific skill
     */
    public function update(Request $request, $skillId)
    {
        $user = $request->user();

        $skill = Skill::where('id', $skillId)
                     ->where('organization_id', $user->id)
                     ->where('skill_type', 'organization_specific')
                     ->firstOrFail();

        $data = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('skills')->where(function ($query) use ($user) {
                    return $query->where('organization_id', $user->id);
                })->ignore($skill->id)
            ],
            'description' => 'nullable|string',
            'category' => 'sometimes|required|string|max:100',
            'required_proficiency_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $skill->update($data);

        return response()->json($skill);
    }

    /**
     * Delete an organization-specific skill
     */
    public function destroy(Request $request, $skillId)
    {
        $user = $request->user();

        $skill = Skill::where('id', $skillId)
                     ->where('organization_id', $user->id)
                     ->where('skill_type', 'organization_specific')
                     ->firstOrFail();

        // Check if skill is being used in any opportunities
        $opportunitiesCount = $skill->opportunities()
                                   ->where('organization_id', $user->id)
                                   ->count();

        if ($opportunitiesCount > 0) {
            return response()->json([
                'error' => 'Cannot delete skill that is being used in opportunities',
                'opportunities_count' => $opportunitiesCount
            ], 422);
        }

        $skill->delete();

        return response()->json(['message' => 'Skill deleted successfully']);
    }

    /**
     * Toggle skill active status
     */
    public function toggleStatus(Request $request, $skillId)
    {
        $user = $request->user();

        $skill = Skill::where('id', $skillId)
                     ->where('organization_id', $user->id)
                     ->where('skill_type', 'organization_specific')
                     ->firstOrFail();

        $skill->update(['is_active' => !$skill->is_active]);

        return response()->json([
            'skill' => $skill,
            'message' => $skill->is_active ? 'Skill activated' : 'Skill deactivated'
        ]);
    }

    /**
     * Get skill categories
     */
    public function categories()
    {
        return response()->json(Skill::getCategories());
    }

    /**
     * Get proficiency levels
     */
    public function proficiencyLevels()
    {
        return response()->json(Skill::getProficiencyLevels());
    }
}
