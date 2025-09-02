<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignature
{
    public function handle(Request $request, Closure $next, ?string $relative = null): Response
    {
        $ignoreQuery = $relative === 'relative';

        if (!URL::hasValidSignature($request, !$ignoreQuery)) {
            return response()->json([
                'message' => 'Invalid signature.'
            ], 403);
        }

        return $next($request);
    }
}