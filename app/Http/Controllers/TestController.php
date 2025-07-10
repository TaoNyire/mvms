<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Opportunity;
use App\Models\User;

class TestController extends Controller
{
    public function test()
    {
        return response()->json([
            'message' => 'API is working',
            'time' => now()->toISOString()
        ]);
    }

    public function getApplications(Request $request)
    {
        try {
            // Simple query without authentication for testing
            $applications = Application::with(['volunteer', 'opportunity'])
                ->limit(10)
                ->get()
                ->map(function($application) {
                    return [
                        'id' => $application->id,
                        'volunteer' => [
                            'id' => $application->volunteer->id,
                            'name' => $application->volunteer->name,
                            'email' => $application->volunteer->email
                        ],
                        'opportunity' => [
                            'id' => $application->opportunity->id,
                            'title' => $application->opportunity->title,
                            'location' => $application->opportunity->location ?? 'No location',
                            'start_date' => $application->opportunity->start_date,
                            'end_date' => $application->opportunity->end_date
                        ],
                        'status' => $application->status,
                        'applied_at' => $application->applied_at,
                        'responded_at' => $application->responded_at
                    ];
                });

            return response()->json([
                'applications' => $applications,
                'total' => $applications->count(),
                'message' => 'Success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get applications',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
