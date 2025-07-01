<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // COUNTERS
        $total_organizations = User::whereHas('roles', fn($q) => $q->where('name', 'Organization'))->count();
        $total_volunteers = User::whereHas('roles', fn($q) => $q->where('name', 'Volunteer'))->count();
        $total_opportunities = Opportunity::count();
        $total_applications = Application::count();

        // TODAY's stats
        $today = Carbon::today();
        $new_organizations_today = User::whereHas('roles', fn($q) => $q->where('name', 'Organization'))
            ->whereDate('created_at', $today)->count();
        $new_volunteers_today = User::whereHas('roles', fn($q) => $q->where('name', 'Volunteer'))
            ->whereDate('created_at', $today)->count();

        // MONTHLY STATS FOR THE YEAR
        $months = [];
        $orgs_monthly = [];
        $vols_monthly = [];
        $opps_monthly = [];
        $apps_monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $label = Carbon::create(null, $m)->format('M');
            $months[] = $label;
            $orgs_monthly[] = User::whereHas('roles', fn($q) => $q->where('name', 'Organization'))
                ->whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
            $vols_monthly[] = User::whereHas('roles', fn($q) => $q->where('name', 'Volunteer'))
                ->whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
            $opps_monthly[] = Opportunity::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
            $apps_monthly[] = Application::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
        }

        // RECENT ACTIVITY (latest 5 applications)
        $recent_applications = Application::with(['volunteer', 'opportunity'])
            ->latest()->limit(5)->get();

        // TOP 5 OPPORTUNITIES BY APPLICATION COUNT
        $top_opportunities = Opportunity::withCount('applications')
            ->orderByDesc('applications_count')->limit(5)->get();

        return response()->json([
            'counters' => [
                'total_organizations' => $total_organizations,
                'total_volunteers' => $total_volunteers,
                'total_opportunities' => $total_opportunities,
                'total_applications' => $total_applications,
                'new_organizations_today' => $new_organizations_today,
                'new_volunteers_today' => $new_volunteers_today,
            ],
            'monthly' => [
                'labels' => $months,
                'organizations' => $orgs_monthly,
                'volunteers' => $vols_monthly,
                'opportunities' => $opps_monthly,
                'applications' => $apps_monthly,
            ],
            'recent_applications' => $recent_applications,
            'top_opportunities' => $top_opportunities,
        ]);
    }
}