<?php

namespace App\Observers;

use App\Models\Guild;
use App\Services\CacheService;

class GuildObserver
{
    public function created(Guild $guild): void
    {
        $this->invalidateCache();
    }

    public function updated(Guild $guild): void
    {
        $this->invalidateCache();
        
        // If ladder points or level changed, invalidate rankings
        if ($guild->isDirty('ladder_point') || $guild->isDirty('level') || $guild->isDirty('exp')) {
            CacheService::invalidate([CacheService::CACHE_TAGS['rankings']]);
        }
    }

    public function deleted(Guild $guild): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        CacheService::invalidateGuildsCache();
    }
}