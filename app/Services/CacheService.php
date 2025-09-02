<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    protected static function getCacheTimes(): array
    {
        return config('metin2.cache.ttl', [
            'short' => 300,
            'medium' => 1800,
            'long' => 3600,
            'daily' => 86400,
            'weekly' => 604800,
        ]);
    }

    protected static function getCacheTags(): array
    {
        return config('metin2.cache.tags', [
            'players' => 'players',
            'guilds' => 'guilds',
            'server' => 'server_status',
            'rankings' => 'rankings',
            'news' => 'news',
            'accounts' => 'accounts',
            'statistics' => 'statistics',
        ]);
    }

    /**
     * Get cached data or execute callback
     */
    public static function remember(string $key, string $duration, callable $callback, array $tags = [])
    {
        $cacheKey = static::generateKey($key);
        $ttl = static::getDuration($duration);

        try {
            if (!empty($tags)) {
                return Cache::tags($tags)->remember($cacheKey, $ttl, $callback);
            }
            
            return Cache::remember($cacheKey, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache remember failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return $callback();
        }
    }

    /**
     * Cache server status
     */
    public static function serverStatus(): array
    {
        return static::remember('server:status', 'short', function () {
            return [
                'status' => 'online',
                'players_online' => \App\Models\Player::online()->count(),
                'total_accounts' => \App\Models\Account::count(),
                'total_guilds' => \App\Models\Guild::count(),
                'uptime_hours' => rand(100, 999),
                'last_update' => now()->toISOString(),
            ];
        }, [static::getCacheTags()['server']]);
    }

    /**
     * Cache server statistics
     */
    public static function serverStatistics(): array
    {
        return static::remember('server:statistics', 'medium', function () {
            return [
                'players' => [
                    'total' => \App\Models\Player::count(),
                    'online' => \App\Models\Player::online()->count(),
                    'by_empire' => [
                        1 => \App\Models\Player::where('empire', 1)->count(),
                        2 => \App\Models\Player::where('empire', 2)->count(),
                        3 => \App\Models\Player::where('empire', 3)->count(),
                    ],
                    'by_level_range' => [
                        '1-30' => \App\Models\Player::whereBetween('level', [1, 30])->count(),
                        '31-60' => \App\Models\Player::whereBetween('level', [31, 60])->count(),
                        '61-90' => \App\Models\Player::whereBetween('level', [61, 90])->count(),
                        '91-120' => \App\Models\Player::whereBetween('level', [91, 120])->count(),
                    ],
                ],
                'guilds' => [
                    'total' => \App\Models\Guild::count(),
                    'active' => \App\Models\Guild::active()->count(),
                    'average_level' => round(\App\Models\Guild::avg('level'), 1),
                ],
                'accounts' => [
                    'total' => \App\Models\Account::count(),
                    'active' => \App\Models\Account::active()->count(),
                    'created_today' => \App\Models\Account::whereDate('create_time', today())->count(),
                    'created_this_week' => \App\Models\Account::where('create_time', '>=', now()->subWeek())->count(),
                ],
            ];
        }, [static::getCacheTags()['statistics']]);
    }

    /**
     * Cache top players
     */
    public static function topPlayers(string $type = 'level', int $limit = 10): array
    {
        $key = "players:top:{$type}:{$limit}";
        
        return static::remember($key, 'medium', function () use ($type, $limit) {
            $query = \App\Models\Player::query();

            switch ($type) {
                case 'level':
                    $query->orderBy('level', 'desc')->orderBy('exp', 'desc');
                    break;
                case 'gold':
                    $query->orderBy('gold', 'desc');
                    break;
                case 'playtime':
                    $query->orderBy('playtime', 'desc');
                    break;
            }

            return $query->with('guild')->limit($limit)->get()->toArray();
        }, [static::getCacheTags()['players'], static::getCacheTags()['rankings']]);
    }

    /**
     * Cache top guilds
     */
    public static function topGuilds(string $type = 'ladder', int $limit = 10): array
    {
        $key = "guilds:top:{$type}:{$limit}";
        
        return static::remember($key, 'medium', function () use ($type, $limit) {
            $query = \App\Models\Guild::query();

            switch ($type) {
                case 'ladder':
                    $query->orderBy('ladder_point', 'desc')->orderBy('level', 'desc');
                    break;
                case 'level':
                    $query->orderBy('level', 'desc')->orderBy('exp', 'desc');
                    break;
            }

            return $query->with('master')->limit($limit)->get()->toArray();
        }, [static::getCacheTags()['guilds'], static::getCacheTags()['rankings']]);
    }

    /**
     * Cache featured news
     */
    public static function featuredNews(int $limit = 5): array
    {
        $key = "news:featured:{$limit}";
        
        return static::remember($key, 'long', function () use ($limit) {
            return \DB::table('web_news')
                ->where('is_published', true)
                ->where('is_featured', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'excerpt' => $article->excerpt,
                        'image' => $article->image,
                        'published_at' => $article->published_at,
                        'tags' => json_decode($article->tags, true) ?? [],
                    ];
                })
                ->toArray();
        }, [static::getCacheTags()['news']]);
    }

    /**
     * Invalidate cache by tags
     */
    public static function invalidate(array $tags): void
    {
        try {
            Cache::tags($tags)->flush();
            Log::info('Cache invalidated', ['tags' => $tags]);
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invalidate all player-related cache
     */
    public static function invalidatePlayersCache(): void
    {
        static::invalidate([
            static::getCacheTags()['players'],
            static::getCacheTags()['rankings'],
            static::getCacheTags()['statistics']
        ]);
    }

    /**
     * Invalidate all guild-related cache
     */
    public static function invalidateGuildsCache(): void
    {
        static::invalidate([
            static::getCacheTags()['guilds'],
            static::getCacheTags()['rankings'],
            static::getCacheTags()['statistics']
        ]);
    }

    /**
     * Invalidate news cache
     */
    public static function invalidateNewsCache(): void
    {
        static::invalidate([static::getCacheTags()['news']]);
    }

    /**
     * Warm up cache with essential data
     */
    public static function warmUp(): void
    {
        Log::info('Starting cache warm up');
        
        try {
            // Warm up server status
            static::serverStatus();
            
            // Warm up statistics
            static::serverStatistics();
            
            // Warm up top rankings
            static::topPlayers('level', 10);
            static::topPlayers('gold', 10);
            static::topGuilds('ladder', 10);
            
            // Warm up featured news
            static::featuredNews();
            
            Log::info('Cache warm up completed successfully');
        } catch (\Exception $e) {
            Log::error('Cache warm up failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate cache key with prefix
     */
    private static function generateKey(string $key): string
    {
        return config('cache.prefix') . $key;
    }

    /**
     * Get cache duration in seconds
     */
    private static function getDuration(string $duration): int
    {
        $times = static::getCacheTimes();
        return $times[$duration] ?? $times['medium'];
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection('cache');
            
            $info = $redis->info('memory');
            $keyspace = $redis->info('keyspace');
            
            return [
                'memory_used' => $info['used_memory_human'] ?? 'N/A',
                'memory_peak' => $info['used_memory_peak_human'] ?? 'N/A',
                'keys_count' => $keyspace['db1']['keys'] ?? 0,
                'hit_rate' => static::calculateHitRate(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to retrieve cache statistics'];
        }
    }

    /**
     * Calculate cache hit rate
     */
    private static function calculateHitRate(): string
    {
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection('cache');
            $info = $redis->info('stats');
            
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            if ($total === 0) {
                return '0%';
            }
            
            return round(($hits / $total) * 100, 2) . '%';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}