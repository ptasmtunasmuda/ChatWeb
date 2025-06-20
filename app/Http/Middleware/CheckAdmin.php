<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivityLog;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            // Log unauthorized admin access attempt
            if ($user) {
                UserActivityLog::log($user, 'admin_access_denied', 'Unauthorized admin access attempt', [
                    'route' => $request->route()?->getName(),
                    'url' => $request->fullUrl(),
                ]);
            }

            return response()->json([
                'message' => 'Access denied. Admin privileges required.'
            ], 403);
        }

        return $next($request);
    }
}
