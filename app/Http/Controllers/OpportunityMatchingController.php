<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpportunityMatchingController extends Controller
{
    public function publicIndex()
    {
        return response()->json([
            'message' => 'Public opportunities endpoint',
            'opportunities' => []
        ]);
    }

    public function recommendedForVolunteer(Request $request)
    {
        return response()->json([
            'message' => 'Recommended opportunities for volunteer',
            'opportunities' => []
        ]);
    }
}
