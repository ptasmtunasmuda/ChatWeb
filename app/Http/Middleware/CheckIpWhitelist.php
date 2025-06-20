<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivityLog;

class CheckIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip check if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Check if user is allowed from this IP
        if (!$user->isAllowedFromIp($request->ip())) {
            // Log blocked access attempt
            UserActivityLog::log($user, 'access_blocked', 'Access blocked due to IP restriction', [
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'message' => 'Access denied from this IP address'
            ], 403);
        }

        return $next($request);
    }
}
