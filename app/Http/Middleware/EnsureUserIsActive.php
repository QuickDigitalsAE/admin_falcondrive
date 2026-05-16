<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (int) $user->status === 1) {
            return $next($request);
        }

        // Revoke all API tokens so inactive user is logged out everywhere in API.
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Invalidate all database sessions for this user (if sessions table is used).
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // Ignore when session storage is not database-based.
        }

        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive. Please contact admin.',
            ], 401);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Your account is inactive. Please contact admin.',
        ]);
    }
}

