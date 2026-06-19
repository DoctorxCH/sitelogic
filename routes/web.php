<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\FrontendLoginController;

Route::get('/login', [FrontendLoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [FrontendLoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [FrontendLoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    return view('welcome');
})->middleware('auth')->name('dashboard');
