<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationDashboardController extends Controller
{
    //
     public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        return response()->json([
            'dashboard' => 'organization',
            'user' => $request->user(),
            'stats' => [
                'opportunities_count' => $request->user()->opportunities()->count(),
                // add more as needed
            ],
        ]);
    }
}
