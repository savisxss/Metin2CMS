<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasLinkedAccount()) {
            return response()->json([
                'message' => 'No linked game account found'
            ], 404);
        }

        $account = $user->account;
        $account->load('players.guild', 'safebox');

        $data = [
            'id' => $account->id,
            'login' => $account->login,
            'email' => $account->email,
            'empire' => $account->empire,
            'empire_name' => $account->getEmpireName(),
            'status' => $account->status,
            'coins' => $account->coins,
            'cash' => $account->cash,
            'create_time' => $account->create_time?->toISOString(),
            'last_play' => $account->last_play?->toISOString(),
            'is_online' => $account->isOnline(),
            'total_playtime' => $account->getTotalPlayTime(),
            'players' => $account->players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'level' => $player->level,
                    'job' => $player->job,
                    'job_name' => $player->job_name,
                    'gold' => $player->gold,
                    'playtime' => $player->playtime,
                    'is_online' => $player->isOnline(),
                    'last_play' => $player->last_play?->toISOString(),
                    'guild' => $player->guild ? [
                        'id' => $player->guild->id,
                        'name' => $player->guild->name,
                    ] : null,
                ];
            }),
            'safebox' => $account->safebox ? [
                'size' => $account->safebox->size,
                'gold' => $account->safebox->gold,
                'item_count' => $account->safebox->item_count,
                'has_password' => $account->safebox->hasPassword(),
            ] : null,
        ];

        return response()->json(['data' => $data]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasLinkedAccount()) {
            return response()->json([
                'message' => 'No linked game account found'
            ], 404);
        }

        $validated = $request->validate([
            'email' => ['sometimes', 'email', 'max:255'],
            'current_password' => ['required_with:new_password', 'string'],
            'new_password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
        ]);

        $account = $user->account;

        // Verify current password if changing password
        if (isset($validated['new_password'])) {
            if (!$this->verifyGamePassword($validated['current_password'], $account->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect',
                    'errors' => [
                        'current_password' => ['Current password is incorrect']
                    ]
                ], 422);
            }

            $account->password = $validated['new_password']; // Will be hashed by mutator
        }

        if (isset($validated['email'])) {
            // Check if email is already in use
            $existingAccount = Account::where('email', $validated['email'])
                                    ->where('id', '!=', $account->id)
                                    ->first();
            
            if ($existingAccount) {
                return response()->json([
                    'message' => 'Email already in use',
                    'errors' => [
                        'email' => ['Email already in use by another account']
                    ]
                ], 422);
            }

            $account->email = $validated['email'];
            
            // Update web user email as well
            $user->email = $validated['email'];
            $user->save();
        }

        $account->save();

        return response()->json([
            'message' => 'Account updated successfully'
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasLinkedAccount()) {
            return response()->json([
                'message' => 'No linked game account found'
            ], 404);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $account = $user->account;

        // Verify current password
        if (!$this->verifyGamePassword($validated['current_password'], $account->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['Current password is incorrect']
                ]
            ], 422);
        }

        // Update password
        $account->password = $validated['new_password']; // Will be hashed by mutator
        $account->save();

        // Update web user password as well
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    public function donations(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $perPage = min($request->input('per_page', 10), 50);
        
        $donations = $user->donations()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $donations->items(),
            'meta' => [
                'current_page' => $donations->currentPage(),
                'last_page' => $donations->lastPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
                'total_amount' => $user->getTotalDonations(),
            ],
        ]);
    }

    private function verifyGamePassword(string $password, string $hashedPassword): bool
    {
        $gameHash = '*' . strtoupper(sha1(sha1($password, true)));
        return hash_equals($hashedPassword, $gameHash);
    }
}