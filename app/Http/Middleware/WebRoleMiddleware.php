<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user account is active
        if (!$user->is_active || $user->account_status !== 'active') {
            auth()->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account is not active. Please contact the administrator.'], 403);
            }
            return redirect()->route('login')->withErrors([
                'account' => 'Your account is not active. Please contact the administrator.'
            ]);
        }

        // Check if user has the required role
        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to access this page.'], 403);
            }
            return redirect()->route('login')->withErrors([
                'access' => 'You do not have permission to access this page.'
            ]);
        }

        // For organization users, check if they are approved
        if ($role === 'organization' && $user->organizationProfile) {
            if ($user->organizationProfile->status !== 'approved') {
                // Avoid redirect loop - only redirect if not already on the profile show page
                if ($request->route()->getName() !== 'organization.profile.show') {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Your organization registration is pending approval.'], 403);
                    }
                    return redirect()->route('organization.profile.show')->withErrors([
                        'approval' => 'Your organization registration is pending approval. You will be notified once approved.'
                    ]);
                }
            }
        }

        return $next($request);
    }
}
