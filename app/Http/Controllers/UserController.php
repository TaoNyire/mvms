<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\VolunteerProfile;
use App\Models\OrganizationProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['roles', 'volunteerProfile', 'organizationProfile']);
        if ($request->role) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }
        $users = $query->paginate(20);
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with(['roles', 'volunteerProfile', 'organizationProfile'])->findOrFail($id);
        return response()->json($user);
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->active = !$user->active;
        $user->save();
        return response()->json(['message' => 'User active status updated']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Generate a random password and email it, or use Laravel's reset link
        $newPassword = Str::random(12);
        $user->password = Hash::make($newPassword);
        $user->save();

        // Send password reset email (implement your own notification/mailable)
        // $user->notify(new \App\Notifications\AdminResetPasswordNotification($newPassword));

        return response()->json(['message' => 'Password reset', 'new_password' => $newPassword]);
    }

    public function addVolunteer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
        ]);

        $password = Str::random(12);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
        ]);

        // Assign volunteer role
        $volunteerRole = Role::where('name', 'volunteer')->first();
        if ($volunteerRole) {
            $user->roles()->attach($volunteerRole->id);
        }

        // Create volunteer profile
        VolunteerProfile::create(['user_id' => $user->id]);

        return response()->json([
            'message' => 'Volunteer created successfully',
            'user' => $user->load('roles'),
            'password' => $password
        ], 201);
    }

    public function addOrganization(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'org_name' => 'required|string|max:255',
        ]);

        $password = Str::random(12);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
        ]);

        // Assign organization role
        $orgRole = Role::where('name', 'organization')->first();
        if ($orgRole) {
            $user->roles()->attach($orgRole->id);
        }

        // Create organization profile
        OrganizationProfile::create([
            'user_id' => $user->id,
            'org_name' => $data['org_name'],
            'email' => $data['email'],
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Organization created successfully',
            'user' => $user->load('roles'),
            'password' => $password
        ], 201);
    }
}