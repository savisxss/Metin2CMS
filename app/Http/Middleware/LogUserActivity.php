<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log user activity for important actions
        if (Auth::check() && $this->shouldLog($request)) {
            $user = Auth::user();
            
            Log::channel('activity')->info('User activity', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'timestamp' => now()->toISOString(),
            ]);
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        $method = $request->method();
        $route = $request->route()?->getName();

        // Log POST, PUT, PATCH, DELETE requests
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // Log access to sensitive routes
        $sensitiveRoutes = [
            'admin.*',
            'profile.*',
            'donation.*',
            'voucher.*',
        ];

        foreach ($sensitiveRoutes as $pattern) {
            if ($route && fnmatch($pattern, $route)) {
                return true;
            }
        }

        return false;
    }
}