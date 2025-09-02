<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'empire' => ['nullable', 'integer', 'in:1,2,3'],
            'job' => ['nullable', 'integer', 'between:0,9'],
            'sort_by' => ['nullable', 'string', Rule::in(['level', 'exp', 'gold', 'playtime'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Player::query();

        // Apply filters
        if ($request->filled('empire')) {
            $query->where('empire', $request->empire);
        }

        if ($request->filled('job')) {
            $query->where('job', $request->job);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'level');
        $sortDirection = $request->input('sort_direction', 'desc');

        if ($sortBy === 'level') {
            $query->orderBy('level', $sortDirection)
                  ->orderBy('exp', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = min($request->input('per_page', 20), 100);
        $players = $query->with('guild')
                        ->paginate($perPage);

        return response()->json([
            'data' => $players->items(),
            'meta' => [
                'current_page' => $players->currentPage(),
                'last_page' => $players->lastPage(),
                'per_page' => $players->perPage(),
                'total' => $players->total(),
                'from' => $players->firstItem(),
                'to' => $players->lastItem(),
            ],
            'links' => [
                'first' => $players->url(1),
                'last' => $players->url($players->lastPage()),
                'prev' => $players->previousPageUrl(),
                'next' => $players->nextPageUrl(),
            ],
        ]);
    }

    public function show(Request $request, Player $player): JsonResponse
    {
        $player->load(['guild', 'account']);

        $data = [
            'id' => $player->id,
            'name' => $player->name,
            'level' => $player->level,
            'exp' => $player->exp,
            'job' => $player->job,
            'job_name' => $player->job_name,
            'empire' => $player->empire,
            'empire_name' => $player->empire_name,
            'gold' => $player->gold,
            'alignment' => $player->alignment,
            'playtime' => $player->playtime,
            'playtime_hours' => $player->playtime_hours,
            'last_play' => $player->last_play?->toISOString(),
            'is_online' => $player->isOnline(),
            'guild' => $player->guild ? [
                'id' => $player->guild->id,
                'name' => $player->guild->name,
                'level' => $player->guild->level,
            ] : null,
        ];

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Player::class);

        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:account,id'],
            'name' => ['required', 'string', 'max:24', 'unique:player,name'],
            'job' => ['required', 'integer', 'between:0,9'],
            'empire' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $player = Player::create($validated);
        $player->load('guild');

        return response()->json([
            'message' => 'Player created successfully',
            'data' => $player
        ], 201);
    }

    public function update(Request $request, Player $player): JsonResponse
    {
        $this->authorize('update', $player);

        $validated = $request->validate([
            'level' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'exp' => ['sometimes', 'integer', 'min:0'],
            'gold' => ['sometimes', 'integer', 'min:0'],
            'alignment' => ['sometimes', 'integer'],
            'guild_id' => ['sometimes', 'nullable', 'integer', 'exists:guild,id'],
        ]);

        $player->update($validated);
        $player->load('guild');

        return response()->json([
            'message' => 'Player updated successfully',
            'data' => $player
        ]);
    }

    public function destroy(Player $player): JsonResponse
    {
        $this->authorize('delete', $player);

        $player->delete();

        return response()->json([
            'message' => 'Player deleted successfully'
        ]);
    }

    public function top(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(['level', 'gold', 'playtime'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $type = $request->input('type');
        $limit = min($request->input('limit', 10), 50);

        $query = Player::query();

        switch ($type) {
            case 'level':
                $query->orderBy('level', 'desc')->orderBy('exp', 'desc');
                break;
            case 'gold':
                $query->orderBy('gold', 'desc');
                break;
            case 'playtime':
                $query->orderBy('playtime', 'desc');
                break;
        }

        $players = $query->with('guild')->limit($limit)->get();

        return response()->json(['data' => $players]);
    }
}