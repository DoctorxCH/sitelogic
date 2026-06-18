<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ChecklistController;

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/checklists', [ChecklistController::class, 'index']);
Route::post('/jobs', [JobController::class, 'store']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
