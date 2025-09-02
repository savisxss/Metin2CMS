<?php

namespace App\Livewire\News;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WebSetting;

class NewsList extends Component
{
    use WithPagination;

    public $search = '';
    public $tag = '';
    public $perPage = 6;

    protected $queryString = [
        'search' => ['except' => ''],
        'tag' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTag()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = \DB::table('web_news')
            ->where('is_published', true)
            ->where('published_at', '<=', now());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tag) {
            $query->whereJsonContains('tags', $this->tag);
        }

        $news = $query->orderBy('published_at', 'desc')
                     ->paginate($this->perPage);

        // Get all available tags
        $allTags = \DB::table('web_news')
            ->where('is_published', true)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->filter()
            ->values();

        return view('livewire.news.news-list', [
            'news' => $news,
            'allTags' => $allTags,
        ]);
    }
}