<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VolunteerSkillController extends Controller
{
    // Attach (replace) volunteer's skills
    public function update(Request $request)
    {
        $profile = $request->user()->volunteerProfile;
        $data = $request->validate([
            'skills' => 'required|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);
        $profile->skills()->sync($data['skills']);
        return response()->json(['message' => 'Skills updated', 'skills' => $profile->skills]);
    }
}
