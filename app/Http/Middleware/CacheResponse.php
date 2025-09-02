<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Don't cache authenticated requests
        if ($request->user()) {
            return $next($request);
        }

        // Don't cache requests with query parameters that change frequently
        if ($request->has(['page', 'search', 'sort_direction'])) {
            return $next($request);
        }

        $key = $this->getCacheKey($request);

        // Try to get from cache
        $cachedResponse = Cache::get($key);
        if ($cachedResponse) {
            return response($cachedResponse['content'])
                ->withHeaders($cachedResponse['headers'])
                ->setStatusCode($cachedResponse['status']);
        }

        $response = $next($request);

        // Only cache successful responses
        if ($response->isSuccessful()) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'headers' => $response->headers->all(),
                'status' => $response->getStatusCode(),
            ], $ttl);
        }

        return $response;
    }

    private function getCacheKey(Request $request): string
    {
        $uri = $request->getRequestUri();
        $queryString = $request->getQueryString();
        
        return 'response_cache:' . md5($uri . '?' . $queryString);
    }
}