<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VolunteerProfile;

class VolunteerProfileController extends Controller
{
    // Fetch authenticated volunteer's profile // Fetch authenticated volunteer's profile with skills
    public function show(Request $request)
    {
        // Eager load skills
        $profile = $request->user()->volunteerProfile()->with('skills')->first();
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // Fields to check
        $fields = ['bio', 'location', 'district', 'region', 'availability'];
        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($profile->$field)) {
                $filled++;
            }
        }
        $completion = round(($filled / count($fields)) * 100);

        // Return with completion percentage and skills
        $data = $profile->toArray();
        $data['completion'] = $completion;

        return response()->json($data);
    }
    // Store or update authenticated volunteer's profile
    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'bio' => 'nullable|string',
            'location' => 'nullable|string',
            'district' => 'nullable|string',
            'region' => 'nullable|string',
            'availability' => 'nullable|string',
        ]);

        $profile = VolunteerProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json([
            'message' => 'Profile saved',
            'profile' => $profile
        ]);
    }
}