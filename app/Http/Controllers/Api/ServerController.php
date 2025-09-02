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
        $stats = Cache::remember('server_status', 60, function () {
            return [
                'status' => 'online',
                'players_online' => Player::online()->count(),
                'total_accounts' => Account::count(),
                'total_guilds' => Guild::count(),
                'uptime_hours' => rand(100, 999), // Mock uptime
                'last_update' => now()->toISOString(),
            ];
        });

        return response()->json([
            'data' => $stats
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $stats = Cache::remember('server_statistics', 300, function () {
            return [
                'players' => [
                    'total' => Player::count(),
                    'online' => Player::online()->count(),
                    'by_empire' => [
                        1 => Player::where('empire', 1)->count(),
                        2 => Player::where('empire', 2)->count(),
                        3 => Player::where('empire', 3)->count(),
                    ],
                    'by_level_range' => [
                        '1-30' => Player::whereBetween('level', [1, 30])->count(),
                        '31-60' => Player::whereBetween('level', [31, 60])->count(),
                        '61-90' => Player::whereBetween('level', [61, 90])->count(),
                        '91-120' => Player::whereBetween('level', [91, 120])->count(),
                    ],
                ],
                'guilds' => [
                    'total' => Guild::count(),
                    'active' => Guild::active()->count(),
                    'average_level' => round(Guild::avg('level'), 1),
                ],
                'accounts' => [
                    'total' => Account::count(),
                    'active' => Account::active()->count(),
                    'created_today' => Account::whereDate('create_time', today())->count(),
                    'created_this_week' => Account::where('create_time', '>=', now()->subWeek())->count(),
                ],
            ];
        });

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