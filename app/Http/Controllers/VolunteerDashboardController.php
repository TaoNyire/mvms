<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VolunteerDashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        return response()->json([
            'dashboard' => 'volunteer',
            'user' => $request->user(),
            'stats' => [
                'applications_count' => $request->user()->applications()->count(),
                // add more as needed
            ],
        ]);
    }
}
