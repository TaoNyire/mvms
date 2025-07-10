<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SystemLog;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|string', // e.g. 'Volunteer', 'Organization', 'Admin'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        // Attach role (using your own roles table)
        $user->roles()->attach(\App\Models\Role::where('name', $fields['role'])->first());

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => optional($user->roles()->first())->name, // string role for frontend
            ]
        ], 201);
    }

    // Login user
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            // Log failed login attempt
            SystemLog::logFailedLogin($fields['email'], [
                'reason' => !$user ? 'user_not_found' : 'invalid_password',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        // Log successful login
        SystemLog::logLogin($user, 'success', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_time' => now()
        ]);

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => optional($user->roles()->first())->name, // string role for frontend
            ]
        ]);
    }

    // Logout user (invalidate the current token)
    public function logout(Request $request)
    {
        $user = $request->user();

        // Log logout
        SystemLog::logLogout($user);

        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // Get current authenticated user's info
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => optional($user->roles()->first())->name,
        ]);
    }
}