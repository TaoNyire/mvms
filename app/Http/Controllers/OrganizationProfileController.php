<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationProfile;

class OrganizationProfileController extends Controller
{
    // Fetch authenticated organization's profile
    public function show(Request $request)
{
        $user = $request->user();
        $profile = $user->organizationProfile;

        if (!$profile) {
            // Return a default profile structure with user information
            return response()->json([
                'id' => null,
                'user_id' => $user->id,
                'org_name' => $user->name,
                'description' => null,
                'mission' => null,
                'vision' => null,
                'sector' => null,
                'org_type' => null,
                'registration_number' => null,
                'is_registered' => false,
                'physical_address' => null,
                'district' => null,
                'region' => null,
                'email' => $user->email,
                'phone' => null,
                'website' => null,
                'focus_areas' => null,
                'active' => true,
                'status' => 'incomplete',
                'completion' => 5, // Very low completion since only basic info is available
                'profile_exists' => false,
                'created_at' => null,
                'updated_at' => null,
            ]);
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
        $data['profile_exists'] = true;

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