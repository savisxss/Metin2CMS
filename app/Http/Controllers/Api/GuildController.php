<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guild;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GuildController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'sort_by' => ['nullable', 'string', Rule::in(['ladder_point', 'level', 'exp', 'win'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Guild::query();

        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('master', function ($masterQuery) use ($request) {
                      $masterQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'ladder_point');
        $sortDirection = $request->input('sort_direction', 'desc');

        if ($sortBy === 'level') {
            $query->orderBy('level', $sortDirection)
                  ->orderBy('exp', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = min($request->input('per_page', 20), 100);
        $guilds = $query->with(['master', 'members'])
                       ->paginate($perPage);

        return response()->json([
            'data' => $guilds->items(),
            'meta' => [
                'current_page' => $guilds->currentPage(),
                'last_page' => $guilds->lastPage(),
                'per_page' => $guilds->perPage(),
                'total' => $guilds->total(),
                'from' => $guilds->firstItem(),
                'to' => $guilds->lastItem(),
            ],
            'links' => [
                'first' => $guilds->url(1),
                'last' => $guilds->url($guilds->lastPage()),
                'prev' => $guilds->previousPageUrl(),
                'next' => $guilds->nextPageUrl(),
            ],
        ]);
    }

    public function show(Request $request, Guild $guild): JsonResponse
    {
        $guild->load(['master', 'members']);

        $data = [
            'id' => $guild->id,
            'name' => $guild->name,
            'level' => $guild->level,
            'exp' => $guild->exp,
            'ladder_point' => $guild->ladder_point,
            'gold' => $guild->gold,
            'win' => $guild->win,
            'draw' => $guild->draw,
            'loss' => $guild->loss,
            'win_rate' => $guild->win_rate,
            'member_count' => $guild->member_count,
            'online_member_count' => $guild->online_member_count,
            'average_level' => $guild->average_level,
            'master' => $guild->master ? [
                'id' => $guild->master->id,
                'name' => $guild->master->name,
                'level' => $guild->master->level,
                'job_name' => $guild->master->job_name,
            ] : null,
            'members' => $guild->members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'level' => $member->level,
                    'job_name' => $member->job_name,
                    'is_online' => $member->isOnline(),
                ];
            }),
        ];

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Guild::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:24', 'unique:guild,name'],
            'master' => ['required', 'integer', 'exists:player,id'],
        ]);

        $guild = Guild::create($validated);
        $guild->load(['master', 'members']);

        return response()->json([
            'message' => 'Guild created successfully',
            'data' => $guild
        ], 201);
    }

    public function update(Request $request, Guild $guild): JsonResponse
    {
        $this->authorize('update', $guild);

        $validated = $request->validate([
            'level' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'exp' => ['sometimes', 'integer', 'min:0'],
            'ladder_point' => ['sometimes', 'integer', 'min:0'],
            'gold' => ['sometimes', 'integer', 'min:0'],
            'win' => ['sometimes', 'integer', 'min:0'],
            'draw' => ['sometimes', 'integer', 'min:0'],
            'loss' => ['sometimes', 'integer', 'min:0'],
        ]);

        $guild->update($validated);
        $guild->load(['master', 'members']);

        return response()->json([
            'message' => 'Guild updated successfully',
            'data' => $guild
        ]);
    }

    public function destroy(Guild $guild): JsonResponse
    {
        $this->authorize('delete', $guild);

        $guild->delete();

        return response()->json([
            'message' => 'Guild deleted successfully'
        ]);
    }

    public function top(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(['ladder', 'level'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $type = $request->input('type');
        $limit = min($request->input('limit', 10), 50);

        $query = Guild::query();

        switch ($type) {
            case 'ladder':
                $query->orderBy('ladder_point', 'desc')->orderBy('level', 'desc');
                break;
            case 'level':
                $query->orderBy('level', 'desc')->orderBy('exp', 'desc');
                break;
        }

        $guilds = $query->with('master')->limit($limit)->get();

        return response()->json(['data' => $guilds]);
    }
}