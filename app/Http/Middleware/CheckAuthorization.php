<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $providedKey = $request->header('Authentication');
        $expectedKey = env('AUTH_KEY');

        if ($providedKey !== $expectedKey) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated..'
            ], 401);
        }

        return $next($request);
    }
}
