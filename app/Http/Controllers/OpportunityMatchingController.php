<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opportunity;
use App\Models\Skill;
use App\Models\User;
use App\Models\Application;

class OpportunityMatchingController extends Controller
{
    /**
     * Public API: List all opportunities with filters (skill, location, org, date)
     * Excludes opportunities that are in_progress, completed, or already full.
     */
    public function publicIndex(Request $request)
    {
        $query = Opportunity::with(['skills', 'organization.organizationProfile'])
            ->whereNotIn('status', ['in_progress', 'completed']);

        // Filter by skill
        if ($request->filled('skill_id')) {
            $query->whereHas('skills', function ($q) use ($request) {
                $q->where('skills.id', $request->input('skill_id'));
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        // Filter by organization (user id)
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->input('organization_id'));
        }

        // Filter by date (start_date <= date && (end_date >= date OR end_date is null))
        if ($request->filled('date')) {
            $query->whereDate('start_date', '<=', $request->input('date'))
                ->where(function ($q) use ($request) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $request->input('date'));
                });
        }

        // Exclude full opportunities
        $opportunities = $query->get()->filter(function ($opp) {
            $acceptedCount = Application::where('opportunity_id', $opp->id)
                ->where('status', 'accepted')
                ->count();
            return $acceptedCount < $opp->volunteers_needed;
        });

        // Paginate manually since we're filtering after get()
        $perPage = 20;
        $page = request('page', 1);
        $paginated = $opportunities->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paginated,
            'total' => $opportunities->count(),
            'per_page' => $perPage,
            'current_page' => $page,
        ]);
    }

    /**
     * Volunteer API: List recommended opportunities (matching engine)
     * - Skill, location, simple scoring and ranking
     * - Only return opportunities with score >= 70
     * - Excludes in_progress/completed/full opportunities
     * - Also provide a count of matched opportunities
     */
    public function recommendedForVolunteer(Request $request)
    {
        $user = $request->user();
        $profile = $user->volunteerProfile;

        if (!$profile) {
            return response()->json(['message' => 'No volunteer profile found'], 404);
        }

        // Get volunteer data
        $volunteerSkills = $profile->skills()->pluck('skills.id')->toArray();
        $volunteerLocation = $profile->location;
        $volunteerRegion = $profile->region ?? null;
        $volunteerAvailability = $profile->availability ?? null;

        // Get all opportunities not in_progress/completed
        $opportunities = Opportunity::with(['skills', 'organization.organizationProfile'])
            ->whereNotIn('status', ['in_progress', 'completed'])
            ->get();

        $results = [];
        foreach ($opportunities as $opp) {
            // Exclude full
            $acceptedCount = Application::where('opportunity_id', $opp->id)
                ->where('status', 'accepted')
                ->count();
            if ($acceptedCount >= $opp->volunteers_needed) continue;

            $oppSkills = $opp->skills->pluck('id')->toArray();

            // Skill score: ratio of required skills matched
            $skillsMatched = count(array_intersect($volunteerSkills, $oppSkills));
            $skillsTotal = count($oppSkills) ?: 1;
            $skillScore = ($skillsMatched / $skillsTotal) * 60;

            // Location score
            $locScore = 0;
            if ($volunteerLocation && strcasecmp($opp->location, $volunteerLocation) == 0) {
                $locScore = 30;
            } elseif ($volunteerRegion && isset($opp->organization->organizationProfile->region) && strcasecmp($opp->organization->organizationProfile->region, $volunteerRegion) == 0) {
                $locScore = 15;
            }

            // Availability score - can be extended, simple example
            $availScore = 0;
            if ($volunteerAvailability && isset($opp->start_date)) {
                if (stripos($volunteerAvailability, 'weekend') !== false) {
                    $weekDay = date('N', strtotime($opp->start_date));
                    if ($weekDay == 6 || $weekDay == 7) {
                        $availScore = 10;
                    }
                }
            }

            $score = $skillScore + $locScore + $availScore;

            // Only include opportunities with score >= 60
            if ($score >= 60) {
                $results[] = [
                    'opportunity' => $opp,
                    'score' => round($score),
                ];
            }
        }

        // Order by score descending
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Optionally, limit to top N matches
        $results = array_slice($results, 0, 20);

        // Count of matched opportunities
        $count = count($results);

        return response()->json([
            'matches' => $results,
            'count' => $count,
        ]);
    }
}