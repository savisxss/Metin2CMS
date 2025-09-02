<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'exp' => $this->exp,
            'job' => $this->job,
            'job_name' => $this->job_name,
            'empire' => $this->empire,
            'empire_name' => $this->empire_name,
            'gold' => $this->gold,
            'formatted_gold' => $this->formatted_gold,
            'alignment' => $this->alignment,
            'playtime' => $this->playtime,
            'playtime_hours' => $this->playtime_hours,
            'last_play' => $this->last_play?->toISOString(),
            'is_online' => $this->isOnline(),
            'rank' => $this->when($request->has('include_rank'), $this->rank),
            'guild' => new GuildResource($this->whenLoaded('guild')),
            'account' => new AccountResource($this->whenLoaded('account')),
        ];
    }
}

class GuildResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'exp' => $this->exp,
            'ladder_point' => $this->ladder_point,
            'gold' => $this->gold,
            'formatted_gold' => $this->formatted_gold,
            'win' => $this->win,
            'draw' => $this->draw,
            'loss' => $this->loss,
            'win_rate' => $this->win_rate,
            'member_count' => $this->member_count,
            'online_member_count' => $this->when($request->has('include_online'), $this->online_member_count),
            'average_level' => $this->when($request->has('include_stats'), $this->average_level),
            'rank' => $this->when($request->has('include_rank'), $this->rank),
            'master' => new PlayerResource($this->whenLoaded('master')),
            'members' => PlayerResource::collection($this->whenLoaded('members')),
        ];
    }
}

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->when($request->user()?->id === $this->id, $this->email),
            'empire' => $this->empire,
            'empire_name' => $this->getEmpireName(),
            'status' => $this->status,
            'coins' => $this->when($request->user()?->account_id === $this->id, $this->coins),
            'cash' => $this->when($request->user()?->account_id === $this->id, $this->cash),
            'create_time' => $this->create_time?->toISOString(),
            'last_play' => $this->last_play?->toISOString(),
            'is_online' => $this->isOnline(),
            'total_playtime' => $this->when($request->has('include_stats'), $this->getTotalPlayTime()),
        ];
    }
}