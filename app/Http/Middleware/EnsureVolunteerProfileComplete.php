<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVolunteerProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for volunteers
        if ($user && $user->hasRole('volunteer')) {
            $profile = $user->volunteerProfile;

            // If no profile exists or profile is not complete
            if (!$profile || !$profile->is_complete) {
                // Allow access to profile completion routes
                $allowedRoutes = [
                    'volunteer.profile.create',
                    'volunteer.profile.store',
                    'volunteer.profile.show',
                    'logout'
                ];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Profile completion required',
                            'redirect' => route('volunteer.profile.create')
                        ], 403);
                    }

                    return redirect()->route('volunteer.profile.create')
                        ->with('warning', 'Please complete your profile to access volunteer opportunities.');
                }
            }
        }

        return $next($request);
    }
}
