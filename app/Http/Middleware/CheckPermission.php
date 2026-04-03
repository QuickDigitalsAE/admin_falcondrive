<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\PermissionMiddleware as SpatiePermissionMiddleware;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        // Super admin bypass
        if ($user && $user->id === 1) {
            return $next($request);
        }

        // Fallback to Spatie permission check
        return app(SpatiePermissionMiddleware::class)->handle($request, $next, $permission);
    }
}
