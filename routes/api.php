<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\GuildController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\AccountController;

Route::middleware(['api', 'rate_limit:api'])->group(function () {
    
    // Server endpoints
    Route::prefix('server')->group(function () {
        Route::get('/status', [ServerController::class, 'status']);
        Route::get('/statistics', [ServerController::class, 'statistics']);
        Route::get('/rates', [ServerController::class, 'rates']);
        Route::get('/ping', [ServerController::class, 'ping']);
    });
    
    // Players endpoints
    Route::prefix('players')->group(function () {
        Route::get('/', [PlayerController::class, 'index']);
        Route::get('/top', [PlayerController::class, 'top']);
        Route::get('/{player}', [PlayerController::class, 'show']);
    });
    
    // Guilds endpoints
    Route::prefix('guilds')->group(function () {
        Route::get('/', [GuildController::class, 'index']);
        Route::get('/top', [GuildController::class, 'top']);
        Route::get('/{guild}', [GuildController::class, 'show']);
    });
    
    // News endpoints
    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/featured', [NewsController::class, 'featured']);
        Route::get('/{id}', [NewsController::class, 'show']);
    });
});

// Protected API routes
Route::middleware(['auth:sanctum', 'rate_limit:api'])->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'data' => $request->user()->load('account')
        ]);
    });
    
    // Account management
    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'show']);
        Route::patch('/', [AccountController::class, 'update']);
        Route::post('/change-password', [AccountController::class, 'changePassword']);
        Route::get('/donations', [AccountController::class, 'donations']);
    });
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Player management
        Route::post('/players', [PlayerController::class, 'store']);
        Route::patch('/players/{player}', [PlayerController::class, 'update']);
        Route::delete('/players/{player}', [PlayerController::class, 'destroy']);
        
        // Guild management
        Route::post('/guilds', [GuildController::class, 'store']);
        Route::patch('/guilds/{guild}', [GuildController::class, 'update']);
        Route::delete('/guilds/{guild}', [GuildController::class, 'destroy']);
        
        // News management
        Route::post('/news', [NewsController::class, 'store']);
        Route::patch('/news/{id}', [NewsController::class, 'update']);
        Route::delete('/news/{id}', [NewsController::class, 'destroy']);
    });
});