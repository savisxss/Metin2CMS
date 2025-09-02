<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\GuildController;
use App\Http\Controllers\Api\NewsController;

Route::middleware(['api', 'throttle:api'])->group(function () {
    
    // Public API routes
    Route::get('/players', [PlayerController::class, 'index']);
    Route::get('/players/{player}', [PlayerController::class, 'show']);
    
    Route::get('/guilds', [GuildController::class, 'index']);
    Route::get('/guilds/{guild}', [GuildController::class, 'show']);
    
    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/{news}', [NewsController::class, 'show']);
    
    // Server status
    Route::get('/status', function () {
        return response()->json([
            'status' => 'online',
            'players_online' => cache()->remember('players_online', 60, function () {
                return random_int(50, 200); // Replace with actual query
            }),
            'timestamp' => now()->toISOString()
        ]);
    });
});

// Protected API routes
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::apiResource('players', PlayerController::class)->except(['index', 'show']);
        Route::apiResource('guilds', GuildController::class)->except(['index', 'show']);
        Route::apiResource('news', NewsController::class)->except(['index', 'show']);
    });
});