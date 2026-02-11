<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip session check for login and logout endpoints
        if ($request->is('api/login') || $request->is('api/logout') || $request->is('api/session/*')) {
            return $next($request);
        }

        // Get token from Authorization header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        // Find user by token
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }

        // Use User model methods for session check
        if (!$user->isSessionActive()) {
            return response()->json([
                'message' => 'Session expired due to inactivity',
                'expired' => true,
                'timeout_minutes' => $user->getSessionTimeoutMinutes(),
                'can_logout' => true
            ], 401);
        }

        // Update last activity (only every minute to reduce DB load)
        if (!$user->last_activity_at || now()->diffInSeconds($user->last_activity_at) > 60) {
            $user->updateLastActivity();
        }

        // Add user info to request for controllers to use
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}
