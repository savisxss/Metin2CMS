<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheStats extends Command
{
    protected $signature = 'cache:stats';
    protected $description = 'Display cache statistics and information';

    public function handle()
    {
        $this->info('Cache Statistics');
        $this->line('================');

        // Get cache configuration
        $driver = config('cache.default');
        $this->info("Current Driver: {$driver}");
        
        // Get cache statistics
        $stats = CacheService::getStats();
        
        if (isset($stats['error'])) {
            $this->error($stats['error']);
            return Command::FAILURE;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Memory Used', $stats['memory_used']],
                ['Peak Memory', $stats['memory_peak']],
                ['Total Keys', $stats['keys_count']],
                ['Hit Rate', $stats['hit_rate']],
            ]
        );

        // Test cache performance
        $this->line('');
        $this->info('Testing Cache Performance...');
        
        $testKey = 'test_performance_' . time();
        $testData = ['data' => str_repeat('test', 1000)];
        
        // Write test
        $startTime = microtime(true);
        Cache::put($testKey, $testData, 60);
        $writeTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Read test
        $startTime = microtime(true);
        $result = Cache::get($testKey);
        $readTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Cleanup
        Cache::forget($testKey);
        
        $this->table(
            ['Operation', 'Time (ms)'],
            [
                ['Write', $writeTime],
                ['Read', $readTime],
            ]
        );

        // Show cache tags info
        $this->line('');
        $this->info('Cache Tags Configuration:');
        $tags = config('cache.tags');
        foreach ($tags as $key => $tag) {
            $this->line("  {$key}: {$tag}");
        }

        return Command::SUCCESS;
    }
}