<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\VolunteerProfile;
use App\Models\OrganizationProfile;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Show registration form
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Handle web login
    public function webLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = User::with('roles')->find(Auth::id());
            $userRole = $user->roles->first();

            // Determine redirect URL based on role
            $redirectUrl = $this->getRedirectUrlByRole($userRole ? $userRole->name : null);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $redirectUrl,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $userRole ? $userRole->name : null,
                    ]
                ]);
            }

            return redirect()->intended($redirectUrl);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Invalid login credentials'
        ])->withInput($request->only('email'));
    }

    // Get redirect URL based on user role
    private function getRedirectUrlByRole($role)
    {
        switch ($role) {
            case 'admin':
                return route('admin.dashboard');
            case 'organization':
                return route('organization.dashboard');
            case 'volunteer':
                return route('volunteer.dashboard');
            default:
                return route('dashboard'); // fallback
        }
    }

    // Handle web registration
    public function webRegister(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|string|in:volunteer,organization',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);

        // Attach role
        $role = Role::where('name', $fields['role'])->first();
        if ($role) {
            $user->roles()->attach($role);
        }

        // Create profile based on role
        if ($fields['role'] === 'volunteer') {
            VolunteerProfile::create(['user_id' => $user->id]);
        } elseif ($fields['role'] === 'organization') {
            OrganizationProfile::create([
                'user_id' => $user->id,
                'org_name' => $fields['name'],
                'email' => $fields['email'],
                'status' => 'pending'
            ]);
        }

        // Login the user
        Auth::login($user);

        // Get redirect URL based on role
        $redirectUrl = $this->getRedirectUrlByRole($fields['role']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'redirect' => $redirectUrl,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $fields['role'],
                ]
            ], 201);
        }

        return redirect()->to($redirectUrl);
    }

    // Handle web logout
    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }

        return redirect()->route('login');
    }

    // Dashboard methods
    public function dashboard()
    {
        $user = Auth::user();
        $userRole = $user->roles->first();

        // Redirect to appropriate dashboard based on role
        if ($userRole) {
            return redirect()->to($this->getRedirectUrlByRole($userRole->name));
        }

        return view('dashboard');
    }

    public function adminDashboard()
    {
        return view('dashboards.admin');
    }

    public function organizationDashboard()
    {
        return view('dashboards.organization');
    }

    public function volunteerDashboard()
    {
        return view('dashboards.volunteer');
    }

    // API methods for backward compatibility
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);

        $role = Role::where('name', $fields['role'])->first();
        if ($role) {
            $user->roles()->attach($role);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => optional($user->roles()->first())->name,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => optional($user->roles()->first())->name,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

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