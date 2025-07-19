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
            try {
                $profile = $user->volunteerProfile;

                // If no profile exists or profile is not complete (less than 60%)
                if (!$profile || $profile->completion_percentage < 60) {
                    // Allow access to profile completion routes
                    $allowedRoutes = [
                        'volunteer.profile.create',
                        'volunteer.profile.store',
                        'volunteer.profile.show',
                        'volunteer.profile.edit',
                        'volunteer.profile.update',
                        'volunteer.profile.quick-complete',
                        'logout',
                        'dashboard' // Allow access to dashboard
                    ];

                    if (!in_array($request->route()->getName(), $allowedRoutes)) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'message' => 'Profile completion required',
                                'redirect' => route('volunteer.profile.create')
                            ], 403);
                        }

                        // Store the intended URL so user can be redirected back after profile completion
                        session(['url.intended' => $request->url()]);

                        return redirect()->route('volunteer.profile.create')
                            ->with('warning', 'Please complete your profile to access volunteer opportunities.');
                    }
                }
            } catch (\Exception $e) {
                // If there's an error checking the profile, redirect to profile creation
                return redirect()->route('volunteer.profile.create')
                    ->with('error', 'Please complete your profile to continue.');
            }
        }

        return $next($request);
    }
}
