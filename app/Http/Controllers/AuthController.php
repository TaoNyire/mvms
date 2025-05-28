<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $role = Role::where('name', $validated['role'])->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Selected role does not exist. Please contact support.',
            ], 422);
        }

        try {
            $user->roles()->attach($role->id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User created, but failed to assign role. Please contact support.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Role assigned: ' . $role->name,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'), // Return as array for consistency
            ]
        ], 201);
    }

    // Login registered user
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $roles = $user->roles->pluck('name');

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
            ]
        ]);
    }

    // Logout user
    public function logout(Request $request)
    {
        // More memory efficient: delete tokens via direct DB query
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $request->user()->id)
            ->where('tokenable_type', get_class($request->user()))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    // Handle forgotten password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 400);
        }
    }

    // Handle the password reset
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // Efficient token delete
                DB::table('personal_access_tokens')
                    ->where('tokenable_id', $user->id)
                    ->where('tokenable_type', get_class($user))
                    ->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 400);
        }
    }

    // Return authenticated user's info (memory safe)
    public function me(Request $request)
    {
        $user = $request->user()->load('roles'); // Only load roles

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            // Optionally add profile summaries here, but only specific fields!
        ]);
    }
}