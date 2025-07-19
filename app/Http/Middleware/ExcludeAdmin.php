<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExcludeAdmin
{
    /**
     * Handle an incoming request.
     * Prevent admin users from accessing certain routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user has admin role
        if ($user->hasRole('admin')) {
            // Redirect admin users away from communication features
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied. Communication features are not available for administrators.',
                    'redirect' => route('admin.dashboard')
                ], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('warning', 'Communication features are not available for administrators.');
        }

        return $next($request);
    }
}
