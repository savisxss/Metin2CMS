<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class CacheWarmUp extends Command
{
    protected $signature = 'cache:warm-up {--force : Force cache refresh}';
    protected $description = 'Warm up cache with essential data';

    public function handle()
    {
        $this->info('Starting cache warm up...');
        
        if ($this->option('force')) {
            $this->warn('Force refresh enabled - clearing existing cache');
            $this->call('cache:clear');
        }

        $startTime = microtime(true);
        
        CacheService::warmUp();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->info("Cache warm up completed in {$duration} seconds");
        
        // Show cache statistics
        $stats = CacheService::getStats();
        if (!isset($stats['error'])) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Memory Used', $stats['memory_used']],
                    ['Peak Memory', $stats['memory_peak']],
                    ['Keys Count', $stats['keys_count']],
                    ['Hit Rate', $stats['hit_rate']],
                ]
            );
        }

        return Command::SUCCESS;
    }
}