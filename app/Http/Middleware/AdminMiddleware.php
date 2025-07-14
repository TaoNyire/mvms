<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. Admin privileges required.'], 403);
            }
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Check if admin account is active
        if (!Auth::user()->is_active || Auth::user()->account_status !== 'active') {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Admin account is not active.'], 403);
            }
            return redirect()->route('login')->with('error', 'Admin account is not active.');
        }

        return $next($request);
    }
}
