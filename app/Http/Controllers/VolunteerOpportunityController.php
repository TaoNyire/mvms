<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Opportunity;
use App\Models\Application;

class VolunteerOpportunityController extends Controller
{
    /**
     * Display opportunities for volunteers with matching engine
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $volunteer = $user->volunteerProfile;

        $query = Opportunity::active()
            ->with(['organization', 'applications'])
            ->where('organization_id', '!=', $user->id); // Exclude own opportunities

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        if ($request->filled('provides_transport')) {
            $query->where('provides_transport', $request->boolean('provides_transport'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('requirements', 'like', "%{$search}%");
            });
        }

        // Skills filter
        if ($request->filled('skills')) {
            $skills = is_array($request->skills) ? $request->skills : [$request->skills];
            $query->withSkills($skills);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort', 'relevance');
        switch ($sortBy) {
            case 'date':
                $query->orderBy('start_date', 'asc');
                break;
            case 'urgency':
                $query->orderByRaw("FIELD(urgency, 'urgent', 'high', 'medium', 'low')");
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'relevance':
            default:
                // We'll handle relevance sorting after getting results
                $query->orderBy('created_at', 'desc');
                break;
        }

        $opportunities = $query->paginate(12);

        // Calculate match scores for relevance sorting
        if ($sortBy === 'relevance' && $volunteer) {
            $opportunities->getCollection()->transform(function ($opportunity) use ($volunteer) {
                $opportunity->match_score = $opportunity->calculateMatchScore($volunteer);
                return $opportunity;
            });

            // Sort by match score
            $sorted = $opportunities->getCollection()->sortByDesc('match_score');
            $opportunities->setCollection($sorted);
        }

        // Get user's applications for these opportunities
        $userApplications = [];
        if ($user) {
            $opportunityIds = $opportunities->pluck('id')->toArray();
            $userApplications = Application::where('volunteer_id', $user->id)
                ->whereIn('opportunity_id', $opportunityIds)
                ->pluck('status', 'opportunity_id')
                ->toArray();
        }

        // Get filter options
        $categories = Opportunity::active()->distinct()->pluck('category')->filter()->sort();
        $districts = Opportunity::active()->distinct()->pluck('district')->filter()->sort();
        $regions = Opportunity::active()->distinct()->pluck('region')->filter()->sort();

        return view('volunteer.opportunities.index', compact(
            'opportunities',
            'userApplications',
            'categories',
            'districts',
            'regions'
        ));
    }

    /**
     * Show opportunity details for volunteers
     */
    public function show(Opportunity $opportunity)
    {
        $user = Auth::user();

        // Check if opportunity is published
        if ($opportunity->status !== 'published') {
            abort(404, 'Opportunity not found.');
        }

        $opportunity->load(['organization.organizationProfile', 'applications']);

        // Increment views count
        $opportunity->incrementViews();

        // Check if user has applied
        $userApplication = null;
        if ($user) {
            $userApplication = Application::where('opportunity_id', $opportunity->id)
                ->where('volunteer_id', $user->id)
                ->first();
        }

        // Calculate match score if user has profile
        $matchScore = 0;
        if ($user && $user->volunteerProfile) {
            $matchScore = $opportunity->calculateMatchScore($user->volunteerProfile);
        }

        // Get similar opportunities
        $similarOpportunities = Opportunity::active()
            ->where('id', '!=', $opportunity->id)
            ->where(function($query) use ($opportunity) {
                $query->where('category', $opportunity->category)
                      ->orWhere('district', $opportunity->district);
            })
            ->limit(3)
            ->get();

        return view('volunteer.opportunities.show', compact(
            'opportunity',
            'userApplication',
            'matchScore',
            'similarOpportunities'
        ));
    }

    /**
     * Get recommended opportunities for volunteer
     */
    public function recommended()
    {
        $user = Auth::user();
        $volunteer = $user->volunteerProfile;

        if (!$volunteer) {
            return redirect()->route('volunteer.profile.create')
                ->with('warning', 'Please complete your profile to see recommended opportunities.');
        }

        $opportunities = Opportunity::active()
            ->with(['organization'])
            ->where('organization_id', '!=', $user->id)
            ->get();

        // Calculate match scores and filter high matches
        $recommendedOpportunities = $opportunities->map(function ($opportunity) use ($volunteer) {
            $opportunity->match_score = $opportunity->calculateMatchScore($volunteer);
            return $opportunity;
        })->filter(function ($opportunity) {
            return $opportunity->match_score >= 60; // Only show opportunities with 60%+ match
        })->sortByDesc('match_score')->take(10);

        // Get user's applications
        $opportunityIds = $recommendedOpportunities->pluck('id')->toArray();
        $userApplications = Application::where('volunteer_id', $user->id)
            ->whereIn('opportunity_id', $opportunityIds)
            ->pluck('status', 'opportunity_id')
            ->toArray();

        return view('volunteer.opportunities.recommended', compact(
            'recommendedOpportunities',
            'userApplications'
        ));
    }

    /**
     * Show application form
     */
    public function apply(Opportunity $opportunity)
    {
        $user = Auth::user();

        // Check if opportunity is still accepting applications
        if (!$opportunity->is_active) {
            return back()->with('error', 'This opportunity is no longer accepting applications.');
        }

        // Check if user already applied
        $existingApplication = Application::where('opportunity_id', $opportunity->id)
            ->where('volunteer_id', $user->id)
            ->first();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied for this opportunity.');
        }

        $opportunity->load(['organization']);

        return view('volunteer.opportunities.apply', compact('opportunity'));
    }
}
