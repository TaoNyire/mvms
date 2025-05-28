<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\Application;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        return response()->json([
            'dashboard' => 'admin',
            'user' => $request->user(),
            'stats' => [
                'users_count' => User::count(),
                'opportunities_count' => Opportunity::count(),
                'applications_count' => Application::count(),
                // add more as needed
            ],
        ]);
    }
}