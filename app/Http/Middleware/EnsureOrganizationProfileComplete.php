<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for organizations
        if ($user && $user->hasRole('organization')) {
            $profile = $user->organizationProfile;

            // If no profile exists or profile is not complete
            if (!$profile || !$profile->is_complete) {
                // Allow access to profile completion routes
                $allowedRoutes = [
                    'organization.profile.create',
                    'organization.profile.store',
                    'organization.profile.show',
                    'organization.profile.quick-complete',
                    'logout'
                ];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Organization profile completion required',
                            'redirect' => route('organization.profile.create')
                        ], 403);
                    }

                    return redirect()->route('organization.profile.create')
                        ->with('warning', 'Please complete your organization profile to access the dashboard.');
                }
            }
        }

        return $next($request);
    }
}
