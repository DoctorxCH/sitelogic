<?php

use App\Http\Controllers\FrontendJobController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Native Authentifizierungs-Routen ohne laravel/ui
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [FrontendJobController::class, 'index'])->name('dashboard');
    Route::get('/jobs/{job}', [FrontendJobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/status', [FrontendJobController::class, 'updateStatus'])->name('jobs.update-status');
    Route::post('/items/{item}/toggle', [FrontendJobController::class, 'toggleItem'])->name('items.toggle');
});
