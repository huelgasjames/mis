<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Simple login that accepts either email or username (name)
     * and returns a token stored in `remember_token`.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $login)->orWhere('name', $login)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate a simple token and persist in remember_token
        $token = Str::random(60);
        $user->remember_token = $token;
        $user->last_activity_at = now();
        $user->save();

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'last_activity_at' => $user->last_activity_at,
            ],
            'session_timeout' => $this->getSessionTimeoutForRole($user->role),
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
                $user->remember_token = null;
                $user->last_activity_at = null;
                $user->save();
            }
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Check session status
     */
    public function sessionStatus(Request $request)
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

        $timeoutMinutes = $this->getSessionTimeoutForRole($user->role);
        $lastActivity = $user->last_activity_at;
        
        if (!$lastActivity) {
            return response()->json([
                'active' => true,
                'role' => $user->role,
                'timeout_minutes' => $timeoutMinutes,
                'last_activity_at' => null,
                'remaining_minutes' => $timeoutMinutes
            ]);
        }

        $elapsedMinutes = now()->diffInMinutes($lastActivity);
        $remainingMinutes = max(0, $timeoutMinutes - $elapsedMinutes);
        $isActive = $elapsedMinutes < $timeoutMinutes;

        return response()->json([
            'active' => $isActive,
            'role' => $user->role,
            'timeout_minutes' => $timeoutMinutes,
            'last_activity_at' => $lastActivity,
            'remaining_minutes' => $remainingMinutes,
            'expired' => !$isActive
        ]);
    }

    /**
     * Ping to keep session alive (called by frontend periodically)
     */
    public function sessionPing(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $timeoutMinutes = $this->getSessionTimeoutForRole($user->role);
        
        // Check if already expired
        if ($user->last_activity_at && now()->diffInMinutes($user->last_activity_at) > $timeoutMinutes) {
            return response()->json([
                'message' => 'Session expired',
                'expired' => true,
                'timeout_minutes' => $timeoutMinutes
            ], 401);
        }

        // Update last activity
        $user->last_activity_at = now();
        $user->save();

        return response()->json([
            'message' => 'Session refreshed',
            'last_activity_at' => $user->last_activity_at,
            'timeout_minutes' => $timeoutMinutes,
            'remaining_minutes' => $timeoutMinutes
        ]);
    }
}
