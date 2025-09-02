<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Guild;

class GuildRanking extends Component
{
    use WithPagination;

    public $sortBy = 'ladder_point';
    public $sortDirection = 'desc';
    public $search = '';
    public $perPage = 20;

    protected $queryString = [
        'sortBy' => ['except' => 'ladder_point'],
        'sortDirection' => ['except' => 'desc'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
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
        $query = Guild::query();

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('master', function ($masterQuery) {
                      $masterQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply sorting
        if (in_array($this->sortBy, ['ladder_point', 'level', 'exp', 'win', 'member_count'])) {
            if ($this->sortBy === 'member_count') {
                $query->withCount('members')
                      ->orderBy('members_count', $this->sortDirection);
            } elseif ($this->sortBy === 'level') {
                $query->orderBy('level', $this->sortDirection)
                      ->orderBy('exp', $this->sortDirection);
            } else {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }
        }

        $guilds = $query->with(['master', 'members'])
                       ->paginate($this->perPage);

        return view('livewire.guild-ranking', [
            'guilds' => $guilds,
        ]);
    }
}