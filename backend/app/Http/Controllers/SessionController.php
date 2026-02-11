<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Get current session status
     */
    public function status(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'active' => false,
                'message' => 'No token provided'
            ], 401);
        }

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json([
                'active' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        $isActive = $user->isSessionActive();
        $remainingMinutes = $user->getRemainingSessionMinutes();

        return response()->json([
            'active' => $isActive,
            'expired' => !$isActive,
            'role' => $user->role,
            'timeout_minutes' => $user->getSessionTimeoutMinutes(),
            'timeout_label' => $user->getSessionTimeoutLabel(),
            'last_activity_at' => $user->last_activity_at,
            'remaining_minutes' => $remainingMinutes,
            'can_logout' => true
        ]);
    }

    /**
     * Ping to keep session alive
     */
    public function ping(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Check if session already expired
        if (!$user->isSessionActive()) {
            return response()->json([
                'message' => 'Session expired',
                'expired' => true,
                'timeout_minutes' => $user->getSessionTimeoutMinutes(),
                'can_logout' => true
            ], 401);
        }

        // Update last activity
        $user->updateLastActivity();

        return response()->json([
            'message' => 'Session refreshed',
            'active' => true,
            'last_activity_at' => $user->last_activity_at,
            'timeout_minutes' => $user->getSessionTimeoutMinutes(),
            'remaining_minutes' => $user->getRemainingSessionMinutes()
        ]);
    }

    /**
     * Extend session (manual refresh)
     */
    public function extend(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Always allow extending if not already expired
        if (!$user->isSessionActive()) {
            return response()->json([
                'message' => 'Session already expired',
                'expired' => true
            ], 401);
        }

        $user->updateLastActivity();

        return response()->json([
            'message' => 'Session extended successfully',
            'active' => true,
            'last_activity_at' => $user->last_activity_at,
            'timeout_minutes' => $user->getSessionTimeoutMinutes(),
            'remaining_minutes' => $user->getRemainingSessionMinutes()
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        
        if ($token) {
            $user = User::where('remember_token', $token)->first();
            if ($user) {
                $user->clearSession();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully',
            'success' => true
        ]);
    }

    /**
     * Get all active sessions (admin only)
     */
    public function activeSessions(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('remember_token', $token)->first();

        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        $activeUsers = User::whereNotNull('remember_token')
            ->whereNotNull('last_activity_at')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'is_active' => $u->isSessionActive(),
                    'remaining_minutes' => $u->getRemainingSessionMinutes(),
                    'last_activity_at' => $u->last_activity_at
                ];
            });

        return response()->json([
            'active_sessions' => $activeUsers,
            'count' => $activeUsers->count()
        ]);
    }

    /**
     * Force logout a user (admin only)
     */
    public function forceLogout(Request $request, $userId)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $admin = User::where('remember_token', $token)->first();

        if (!$admin || !$admin->isAdmin()) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->clearSession();

        return response()->json([
            'message' => 'User logged out successfully',
            'user_id' => $userId
        ]);
    }
}
