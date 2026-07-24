<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\JwtHelper;
use App\Models\BlacklistedToken;

class JwtAuth
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Token missing'
            ], 401);
        }

        // Check blacklist (logged-out token)
        $isBlacklisted = BlacklistedToken::where(
            'token',
            hash('sha256', $token)
        )->exists();

        if ($isBlacklisted) {
            return response()->json([
                'status' => false,
                'message' => 'Token invalid (logged out)'
            ], 401);
        }

        // Validate token
        $decoded = JwtHelper::validateToken($token);

        if (!$decoded || empty($decoded->user_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired token'
            ], 401);
        }

        // Inject user_id into request
        $request->merge(['auth_user_id' => $decoded->user_id]);

        return $next($request);
    }
}
