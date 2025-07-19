<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifiedUserRole
{
    /**
     * Handle an incoming request.
     * Ensures user is authenticated and has a valid role for security and data integrity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            Log::warning('Unauthenticated access attempt to protected route', [
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            return redirect()->route('login')->with('error', 'Please login to access this feature.');
        }

        $user = Auth::user();

        // Check if user account is active
        if (!$user->is_active || $user->account_status !== 'active') {
            Log::warning('Inactive user access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'account_status' => $user->account_status,
                'is_active' => $user->is_active,
                'route' => $request->route()->getName()
            ]);

            Auth::logout();
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Account is not active'], 403);
            }
            
            return redirect()->route('login')->with('error', 'Your account is not active. Please contact support.');
        }

        // Check if user has any valid role
        $userRoles = $user->roles->pluck('name')->toArray();
        $validRoles = ['admin', 'organization', 'volunteer'];
        
        if (empty($userRoles) || !array_intersect($userRoles, $validRoles)) {
            Log::warning('User without valid role access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $userRoles,
                'route' => $request->route()->getName()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid user role'], 403);
            }
            
            return redirect()->route('login')->with('error', 'Your account does not have the required permissions.');
        }

        // Additional security checks for organization users
        if (in_array('organization', $userRoles)) {
            $orgProfile = $user->organizationProfile;
            
            // Check if organization profile exists
            if (!$orgProfile) {
                Log::warning('Organization user without profile access attempt', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'route' => $request->route()->getName()
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Organization profile required'], 403);
                }
                
                return redirect()->route('organization.profile.create')
                    ->with('warning', 'Please complete your organization profile to access this feature.');
            }

            // Check if organization is approved (for sensitive operations)
            if ($orgProfile->status !== 'approved' && $this->requiresApprovedOrganization($request)) {
                Log::info('Unapproved organization access to restricted feature', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'org_status' => $orgProfile->status,
                    'route' => $request->route()->getName()
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Organization approval required'], 403);
                }
                
                return redirect()->route('organization.dashboard')
                    ->with('warning', 'Your organization is pending approval. Some features may be limited.');
            }
        }

        // Additional security checks for volunteer users
        if (in_array('volunteer', $userRoles)) {
            $volunteerProfile = $user->volunteerProfile;

            // Check if volunteer profile exists, but only for shared routes
            // Skip this check for volunteer-specific routes that have their own profile completion middleware
            $routeName = $request->route()->getName();
            $volunteerSpecificRoutes = [
                'volunteer.dashboard',
                'volunteer.profile.create',
                'volunteer.profile.store',
                'volunteer.profile.show',
                'volunteer.profile.edit',
                'volunteer.profile.update',
                'volunteer.profile.quick-complete',
                'volunteer.opportunities.index',
                'volunteer.opportunities.show',
                'volunteer.opportunities.apply',
                'volunteer.opportunities.recommended',
                'volunteer.applications.index',
                'applications.store',
                'applications.withdraw',
                'assignments.show'
            ];

            if (!$volunteerProfile && !in_array($routeName, $volunteerSpecificRoutes)) {
                Log::warning('Volunteer user without profile access attempt to shared route', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'route' => $routeName
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Volunteer profile required'], 403);
                }

                return redirect()->route('volunteer.profile.create')
                    ->with('warning', 'Please complete your volunteer profile to access this feature.');
            }
        }

        // Log successful access for security monitoring
        Log::info('Authenticated user access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'roles' => $userRoles,
            'route' => $request->route()->getName(),
            'ip' => $request->ip()
        ]);

        return $next($request);
    }

    /**
     * Check if the current request requires an approved organization
     */
    private function requiresApprovedOrganization(Request $request): bool
    {
        $restrictedRoutes = [
            'messages.store',
            'announcements.store',
            'opportunities.store',
            'opportunities.publish'
        ];

        $routeName = $request->route()->getName();
        return in_array($routeName, $restrictedRoutes);
    }
}
