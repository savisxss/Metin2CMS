<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string $key = 'global'): Response
    {
        $limits = $this->getLimits($key, $request);

        foreach ($limits as $limit) {
            $response = RateLimiter::attempt(
                $limit->key,
                $limit->maxAttempts,
                function () use ($next, $request) {
                    return $next($request);
                },
                $limit->decayMinutes * 60
            );

            if (!$response) {
                return response()->json([
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => RateLimiter::availableIn($limit->key)
                ], 429);
            }
        }

        return $next($request);
    }

    private function getLimits(string $key, Request $request): array
    {
        $ip = $request->ip();
        $user = $request->user();

        return match ($key) {
            'login' => [
                Limit::perMinute(5)->by($ip),
                Limit::perMinute(10)->by($user?->id ?? $ip),
            ],
            'register' => [
                Limit::perMinute(2)->by($ip),
                Limit::perHour(5)->by($ip),
            ],
            'api' => [
                Limit::perMinute(60)->by($user?->id ?? $ip),
                Limit::perMinute(100)->by($ip),
            ],
            'admin' => [
                Limit::perMinute(30)->by($user?->id ?? $ip),
            ],
            'donation' => [
                Limit::perMinute(10)->by($user?->id ?? $ip),
            ],
            'voucher' => [
                Limit::perMinute(5)->by($user?->id ?? $ip),
                Limit::perHour(20)->by($user?->id ?? $ip),
            ],
            default => [
                Limit::perMinute(120)->by($ip),
            ],
        };
    }
}