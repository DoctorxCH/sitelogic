<?php

use App\Http\Controllers\FrontendJobController;
use Illuminate\Support\Facades\Route;

// Standard Laravel Auth-Routen (Login, Logout)
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [FrontendJobController::class, 'index'])->name('dashboard');
    Route::get('/jobs/{job}', [FrontendJobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/status', [FrontendJobController::class, 'updateStatus'])->name('jobs.update-status');
    Route::post('/items/{item}/toggle', [FrontendJobController::class, 'toggleItem'])->name('items.toggle');
});
