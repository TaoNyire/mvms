<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skill;
use App\Models\SkillMatch;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    /**
     * Get all available skills
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search = $request->get('search');

        $query = Skill::active();

        if ($category) {
            $query->byCategory($category);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $skills = $query->orderBy('category')->orderBy('name')->get();

        return response()->json([
            'skills' => $skills,
            'categories' => Skill::getCategories(),
            'proficiency_levels' => Skill::getProficiencyLevels()
        ]);
    }

    /**
     * Get user's skills
     */
    public function getUserSkills(Request $request)
    {
        $user = Auth::user();
        $skills = $user->skills()->with('pivot')->get();

        $skillsByCategory = $skills->groupBy('category');

        return response()->json([
            'skills' => $skills,
            'skills_by_category' => $skillsByCategory,
            'total_skills' => $skills->count(),
            'categories' => $skills->pluck('category')->unique()->values()
        ]);
    }

    /**
     * Add skill to user
     */
    public function addUserSkill(Request $request)
    {
        $validated = $request->validate([
            'skill_id' => 'required|exists:skills,id',
            'proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'notes' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // Check if user already has this skill
        if ($user->skills()->where('skill_id', $validated['skill_id'])->exists()) {
            return response()->json([
                'message' => 'You already have this skill. Use update instead.'
            ], 422);
        }

        $user->skills()->attach($validated['skill_id'], [
            'proficiency_level' => $validated['proficiency_level'],
            'years_experience' => $validated['years_experience'],
            'notes' => $validated['notes']
        ]);

        // Update skill matches for this user
        SkillMatch::updateMatchesForUser($user);

        // Log the action
        SystemLog::logUserAction('add_skill', 'Skill', $validated['skill_id'], [
            'skill_name' => Skill::find($validated['skill_id'])->name,
            'proficiency_level' => $validated['proficiency_level']
        ]);

        return response()->json([
            'message' => 'Skill added successfully',
            'skill' => $user->skills()->where('skill_id', $validated['skill_id'])->with('pivot')->first()
        ]);
    }

    /**
     * Update user's skill
     */
    public function updateUserSkill(Request $request, $skillId)
    {
        $validated = $request->validate([
            'proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'notes' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        if (!$user->skills()->where('skill_id', $skillId)->exists()) {
            return response()->json([
                'message' => 'Skill not found in your profile'
            ], 404);
        }

        $user->skills()->updateExistingPivot($skillId, [
            'proficiency_level' => $validated['proficiency_level'],
            'years_experience' => $validated['years_experience'],
            'notes' => $validated['notes']
        ]);

        // Update skill matches for this user
        SkillMatch::updateMatchesForUser($user);

        // Log the action
        SystemLog::logUserAction('update_skill', 'Skill', $skillId, [
            'skill_name' => Skill::find($skillId)->name,
            'proficiency_level' => $validated['proficiency_level']
        ]);

        return response()->json([
            'message' => 'Skill updated successfully',
            'skill' => $user->skills()->where('skill_id', $skillId)->with('pivot')->first()
        ]);
    }

    /**
     * Remove skill from user
     */
    public function removeUserSkill(Request $request, $skillId)
    {
        $user = Auth::user();

        if (!$user->skills()->where('skill_id', $skillId)->exists()) {
            return response()->json([
                'message' => 'Skill not found in your profile'
            ], 404);
        }

        $skill = Skill::find($skillId);
        $user->skills()->detach($skillId);

        // Update skill matches for this user
        SkillMatch::updateMatchesForUser($user);

        // Log the action
        SystemLog::logUserAction('remove_skill', 'Skill', $skillId, [
            'skill_name' => $skill->name
        ]);

        return response()->json([
            'message' => 'Skill removed successfully'
        ]);
    }

    /**
     * Get skill matches for user
     */
    public function getSkillMatches(Request $request)
    {
        $user = Auth::user();
        $minScore = $request->get('min_score', 60);
        $limit = $request->get('limit', 10);

        $matches = SkillMatch::where('user_id', $user->id)
            ->where('match_score', '>=', $minScore)
            ->with(['opportunity.organization', 'opportunity.skills'])
            ->orderBy('match_score', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'matches' => $matches->map(function ($match) {
                return [
                    'id' => $match->id,
                    'opportunity' => [
                        'id' => $match->opportunity->id,
                        'title' => $match->opportunity->title,
                        'description' => $match->opportunity->description,
                        'location' => $match->opportunity->location,
                        'start_date' => $match->opportunity->start_date,
                        'end_date' => $match->opportunity->end_date,
                        'organization' => $match->opportunity->organization->name ?? 'Unknown'
                    ],
                    'match_score' => $match->match_score,
                    'match_quality' => $match->match_quality,
                    'match_quality_color' => $match->match_quality_color,
                    'matched_skills' => $match->matched_skills,
                    'missing_skills' => $match->missing_skills,
                    'calculated_at' => $match->calculated_at
                ];
            }),
            'total_matches' => $matches->count()
        ]);
    }

    /**
     * Create a new skill (admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        $skill = Skill::create($validated);

        // Log the action
        SystemLog::logUserAction('create_skill', 'Skill', $skill->id, [
            'skill_name' => $skill->name,
            'category' => $skill->category
        ]);

        return response()->json([
            'message' => 'Skill created successfully',
            'skill' => $skill
        ], 201);
    }

    /**
     * Update a skill (admin only)
     */
    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        $skill->update($validated);

        // Log the action
        SystemLog::logUserAction('update_skill_definition', 'Skill', $skill->id, [
            'skill_name' => $skill->name,
            'category' => $skill->category
        ]);

        return response()->json([
            'message' => 'Skill updated successfully',
            'skill' => $skill
        ]);
    }

    /**
     * Delete a skill (admin only)
     */
    public function destroy(Skill $skill)
    {
        // Check if skill is being used
        if ($skill->users()->count() > 0 || $skill->opportunities()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete skill that is currently in use'
            ], 422);
        }

        $skillName = $skill->name;
        $skill->delete();

        // Log the action
        SystemLog::logUserAction('delete_skill', 'Skill', $skill->id, [
            'skill_name' => $skillName
        ]);

        return response()->json([
            'message' => 'Skill deleted successfully'
        ]);
    }

    /**
     * Recalculate all skill matches
     */
    public function recalculateMatches(Request $request)
    {
        $user = Auth::user();
        SkillMatch::updateMatchesForUser($user);

        return response()->json([
            'message' => 'Skill matches recalculated successfully'
        ]);
    }
}