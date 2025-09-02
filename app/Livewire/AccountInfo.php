<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AccountInfo extends Component
{
    public $account = null;
    public $players = [];
    public $donations = [];
    public $totalPlayTime = 0;
    public $totalDonations = 0;

    public function mount()
    {
        $this->loadAccountData();
    }

    public function refreshData()
    {
        $this->loadAccountData();
        $this->dispatch('account-refreshed');
    }

    private function loadAccountData()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasLinkedAccount()) {
            return;
        }

        $this->account = $user->account;
        
        if ($this->account) {
            $this->players = $this->account->players()
                ->orderBy('level', 'desc')
                ->get()
                ->map(function ($player) {
                    return [
                        'id' => $player->id,
                        'name' => $player->name,
                        'level' => $player->level,
                        'job' => $player->job_name,
                        'empire' => $player->empire_name,
                        'guild' => $player->guild?->name,
                        'gold' => $player->formatted_gold,
                        'playtime' => $player->playtime_hours,
                        'is_online' => $player->isOnline(),
                        'last_play' => $player->last_play?->diffForHumans(),
                    ];
                });

            $this->totalPlayTime = $this->account->getTotalPlayTime();
            
            $this->donations = $user->donations()
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($donation) {
                    return [
                        'id' => $donation->id,
                        'amount' => $donation->amount,
                        'currency' => $donation->currency,
                        'coins' => $donation->coins_amount,
                        'date' => $donation->created_at->format('Y-m-d H:i'),
                        'method' => ucfirst($donation->payment_method),
                    ];
                });

            $this->totalDonations = $user->getTotalDonations();
        }
    }

    public function render()
    {
        return view('livewire.account-info');
    }
}