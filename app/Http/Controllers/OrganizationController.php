<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationProfile;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganizationProfile::query();
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $orgs = $query->with('user')->paginate(20);
        return response()->json($orgs);
    }

    public function show($id)
    {
        $org = OrganizationProfile::with('user')->findOrFail($id);
        return response()->json($org);
    }

    public function approve($id)
    {
        $org = OrganizationProfile::findOrFail($id);
        $org->status = 'verified';
        $org->is_registered = true;
        $org->active = true;
        $org->save();
        // Notify user (optionally)
        return response()->json(['message' => 'Organization approved']);
    }

    public function reject(Request $request, $id)
    {
        $org = OrganizationProfile::findOrFail($id);
        $org->status = 'rejected';
        $org->is_registered = false;
        $org->active = false;
        $org->save();
        // Notify user (optionally)
        return response()->json(['message' => 'Organization rejected']);
    }

    public function toggleActive($id)
    {
        $org = OrganizationProfile::findOrFail($id);
        $org->active = !$org->active;
        $org->save();
        return response()->json(['message' => 'Organization active status updated']);
    }

    public function destroy($id)
    {
        $org = OrganizationProfile::findOrFail($id);
        $org->delete();
        return response()->json(['message' => 'Organization deleted']);
    }
}