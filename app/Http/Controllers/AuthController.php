<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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



    public function organizationDashboard()
    {
        $user = Auth::user();

        // Check if user has organization role
        if (!$user->hasRole('organization')) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        // Check profile completion status
        $profile = $user->organizationProfile;
        $profileIncomplete = !$profile || !$profile->is_complete;

        // Get real dashboard data
        $dashboardData = $this->getOrganizationDashboardData($user);
        $dashboardData['profileIncomplete'] = $profileIncomplete;
        $dashboardData['profile'] = $profile;

        return view('dashboards.organization', $dashboardData);
    }

    private function getOrganizationDashboardData($user)
    {
        // Get opportunities statistics
        $opportunities = $user->opportunities();
        $totalOpportunities = $opportunities->count();
        $publishedOpportunities = $opportunities->where('status', 'published')->count();
        $draftOpportunities = $opportunities->where('status', 'draft')->count();
        $completedOpportunities = $opportunities->where('status', 'completed')->count();

        // Get applications statistics
        $applications = \App\Models\Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        });
        $totalApplications = $applications->count();
        $pendingApplications = $applications->where('status', 'pending')->count();
        $acceptedApplications = $applications->where('status', 'accepted')->count();
        $rejectedApplications = $applications->where('status', 'rejected')->count();

        // Get recent applications
        $recentApplications = \App\Models\Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->with(['volunteer', 'opportunity'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        // Get recent opportunities
        $recentOpportunities = $user->opportunities()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get volunteer count (unique volunteers who applied)
        $volunteerCount = \App\Models\Application::whereHas('opportunity', function($query) use ($user) {
            $query->where('organization_id', $user->id);
        })
        ->distinct('volunteer_id')
        ->count('volunteer_id');

        return [
            'totalOpportunities' => $totalOpportunities,
            'publishedOpportunities' => $publishedOpportunities,
            'draftOpportunities' => $draftOpportunities,
            'completedOpportunities' => $completedOpportunities,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'acceptedApplications' => $acceptedApplications,
            'rejectedApplications' => $rejectedApplications,
            'volunteerCount' => $volunteerCount,
            'recentApplications' => $recentApplications,
            'recentOpportunities' => $recentOpportunities,
        ];
    }

    public function volunteerDashboard()
    {
        $user = Auth::user();

        // Check if user has volunteer role
        if (!$user->hasRole('volunteer')) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $profile = $user->volunteerProfile;

        // Show profile completion warning if profile is incomplete
        // But still allow access to dashboard for profile completion flow
        if (!$profile || !$profile->is_complete) {
            session()->flash('profile_incomplete', true);
            session()->flash('completion_percentage', $profile ? $profile->completion_percentage : 0);
        }

        // Get real dashboard data
        $dashboardData = $this->getVolunteerDashboardData($user);

        return view('dashboards.volunteer', array_merge(compact('profile'), $dashboardData));
    }

    private function getVolunteerDashboardData($user)
    {
        // Get applications statistics
        $applications = $user->applications();
        $totalApplications = $applications->count();
        $pendingApplications = $applications->where('status', 'pending')->count();
        $acceptedApplications = $applications->where('status', 'accepted')->count();
        $rejectedApplications = $applications->where('status', 'rejected')->count();

        // Get recent applications
        $recentApplications = $user->applications()
            ->with(['opportunity.organization'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get available opportunities (not applied to)
        $appliedOpportunityIds = $user->applications()->pluck('opportunity_id');
        $availableOpportunities = \App\Models\Opportunity::published()
            ->where('status', 'published')
            ->whereNotIn('id', $appliedOpportunityIds)
            ->where('application_deadline', '>', now())
            ->with('organization')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get volunteer hours (placeholder - would need task/assignment tracking)
        $volunteerHours = 0; // TODO: Calculate from completed tasks/assignments

        // Get upcoming activities (accepted applications with future dates)
        $upcomingActivities = $user->applications()
            ->where('status', 'accepted')
            ->whereHas('opportunity', function($query) {
                $query->where('start_date', '>', now());
            })
            ->with(['opportunity.organization'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return [
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'acceptedApplications' => $acceptedApplications,
            'rejectedApplications' => $rejectedApplications,
            'volunteerHours' => $volunteerHours,
            'recentApplications' => $recentApplications,
            'availableOpportunities' => $availableOpportunities,
            'upcomingActivities' => $upcomingActivities,
        ];
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

    // Password Reset Methods
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        // Generate reset token
        $token = Str::random(64);

        // Store token in database (you might want to create a password_resets table)
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send email (simplified version)
        try {
            \Mail::send('emails.password-reset', ['token' => $token, 'user' => $user], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Reset Password - MVMS');
            });

            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            return back()->with('status', 'Password reset link has been generated. Check your email.');
        }
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $passwordReset = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset! You can now sign in.');
    }
}