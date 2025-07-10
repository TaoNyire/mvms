<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationProfile;

class OrganizationProfileController extends Controller
{
    // Fetch authenticated organization's profile
    public function show(Request $request)
{
    $profile = $request->user()->organizationProfile;
    if (!$profile) {
        return response()->json(['message' => 'Profile not found'], 404);
    }

    // Fields to check
    $fields = [
        'org_name', 'description', 'mission', 'vision', 'sector', 'org_type',
        'registration_number', 'is_registered', 'physical_address', 'district',
        'region', 'email', 'phone', 'website', 'focus_areas', 'active', 'status'
    ];
    $filled = 0;
    foreach ($fields as $field) {
        if (!empty($profile->$field) || (is_bool($profile->$field))) { // count boolean true/false as filled
            $filled++;
        }
    }
    $completion = round(($filled / count($fields)) * 100);

    // Return with completion percentage
    $data = $profile->toArray();
    $data['completion'] = $completion;

    return response()->json($data);
}

    // Store or update authenticated organization's profile
    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'org_name' => 'required|string',
            'description' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'sector' => 'nullable|string',
            'org_type' => 'nullable|in:NGO,CBO,Government,Faith-based,Educational,Private',
            'registration_number' => 'nullable|string',
            'is_registered' => 'nullable|boolean',
            'physical_address' => 'nullable|string',
            'district' => 'nullable|string',
            'region' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'website' => 'nullable|string',
            'focus_areas' => 'nullable|string',
            'active' => 'nullable|boolean',
            'status' => 'nullable|in:pending,verified,rejected'
        ]);

        // Ensure status has a default value if not provided
        if (!isset($data['status']) || $data['status'] === null) {
            $data['status'] = 'pending';
        }

        $profile = OrganizationProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json([
            'message' => 'Profile saved',
            'profile' => $profile
        ]);
    }
}