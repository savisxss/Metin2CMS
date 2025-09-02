<?php

namespace App\Observers;

use App\Models\Player;
use App\Services\CacheService;

class PlayerObserver
{
    public function created(Player $player): void
    {
        $this->invalidateCache();
    }

    public function updated(Player $player): void
    {
        $this->invalidateCache();
        
        // If level changed, invalidate rankings
        if ($player->isDirty('level') || $player->isDirty('exp')) {
            CacheService::invalidate([CacheService::CACHE_TAGS['rankings']]);
        }
    }

    public function deleted(Player $player): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        CacheService::invalidatePlayersCache();
    }
}