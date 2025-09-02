<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Account;
use App\Models\Guild;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ServerController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $stats = \App\Services\CacheService::serverStatus();

        return response()->json([
            'data' => $stats
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $stats = \App\Services\CacheService::serverStatistics();

        return response()->json([
            'data' => $stats
        ]);
    }

    public function rates(Request $request): JsonResponse
    {
        $rates = app('settings')->get('server_rates', [
            'exp' => 10,
            'yang' => 10,
            'drop' => 10
        ]);

        if (is_string($rates)) {
            $rates = json_decode($rates, true);
        }

        return response()->json([
            'data' => [
                'exp_rate' => $rates['exp'] ?? 10,
                'yang_rate' => $rates['yang'] ?? 10,
                'drop_rate' => $rates['drop'] ?? 10,
                'max_level' => 120,
                'server_name' => app('settings')->get('site_name', 'Metin2 Server'),
            ]
        ]);
    }

    public function ping(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'server_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}