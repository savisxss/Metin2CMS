<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Player;

class PlayerRanking extends Component
{
    use WithPagination;

    public $sortBy = 'level';
    public $sortDirection = 'desc';
    public $empire = '';
    public $job = '';
    public $search = '';
    public $perPage = 20;

    protected $queryString = [
        'sortBy' => ['except' => 'level'],
        'sortDirection' => ['except' => 'desc'],
        'empire' => ['except' => ''],
        'job' => ['except' => ''],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEmpire()
    {
        $this->resetPage();
    }

    public function updatingJob()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = Player::query();

        // Apply filters
        if ($this->empire) {
            $query->where('empire', $this->empire);
        }

        if ($this->job !== '') {
            $query->where('job', $this->job);
        }

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Apply sorting
        if (in_array($this->sortBy, ['level', 'exp', 'gold', 'playtime'])) {
            if ($this->sortBy === 'level') {
                $query->orderBy('level', $this->sortDirection)
                      ->orderBy('exp', $this->sortDirection);
            } else {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }
        }

        $players = $query->with('guild')
                        ->paginate($this->perPage);

        $empires = [
            1 => 'Shinsoo',
            2 => 'Chunjo', 
            3 => 'Jinno'
        ];

        $jobs = [
            0 => 'Warrior (M)',
            1 => 'Ninja (F)',
            2 => 'Sura (M)', 
            3 => 'Shaman (F)',
            4 => 'Warrior (F)',
            5 => 'Ninja (M)',
            6 => 'Sura (F)',
            7 => 'Shaman (M)',
            8 => 'Lycan (M)',
            9 => 'Lycan (F)',
        ];

        return view('livewire.player-ranking', [
            'players' => $players,
            'empires' => $empires,
            'jobs' => $jobs,
        ]);
    }
}