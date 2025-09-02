<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceMode = app('settings')->get('maintenance_mode', false);
        
        if ($maintenanceMode) {
            $user = $request->user();
            
            // Allow admins to access during maintenance
            if (!$user || !$user->hasRole('admin')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Site is under maintenance. Please try again later.'
                    ], 503);
                }
                
                return response()->view('maintenance', [], 503);
            }
        }

        return $next($request);
    }
}