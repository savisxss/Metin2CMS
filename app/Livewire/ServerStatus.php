<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Player;
use Illuminate\Support\Facades\Cache;

class ServerStatus extends Component
{
    public $playersOnline = 0;
    public $totalAccounts = 0;
    public $serverUptime = 0;
    public $isOnline = true;

    public function mount()
    {
        $this->loadServerStats();
    }

    public function refreshStats()
    {
        Cache::forget('server_stats');
        $this->loadServerStats();
        
        $this->dispatch('stats-refreshed');
    }

    private function loadServerStats()
    {
        $stats = Cache::remember('server_stats', 60, function () {
            return [
                'players_online' => Player::online()->count(),
                'total_accounts' => \App\Models\Account::count(),
                'server_uptime' => $this->calculateUptime(),
                'is_online' => $this->checkServerStatus(),
            ];
        });

        $this->playersOnline = $stats['players_online'];
        $this->totalAccounts = $stats['total_accounts'];
        $this->serverUptime = $stats['server_uptime'];
        $this->isOnline = $stats['is_online'];
    }

    private function calculateUptime()
    {
        // Mock uptime - in real implementation, check server process
        return rand(100, 999);
    }

    private function checkServerStatus()
    {
        // Mock status check - in real implementation, ping game server
        return true;
    }

    public function render()
    {
        return view('livewire.server-status');
    }
}