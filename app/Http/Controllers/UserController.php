<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
}