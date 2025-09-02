<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Legacy routes for backward compatibility
Route::get('/news', function () {
    return redirect('/');
})->name('news');

Route::get('/ranking/players', function () {
    return view('ranking.players');
})->name('ranking.players');

Route::get('/ranking/guilds', function () {
    return view('ranking.guilds');
})->name('ranking.guilds');

Route::get('/download', function () {
    return view('download');
})->name('download');

require __DIR__.'/auth.php';