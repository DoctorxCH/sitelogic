<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\PhotoController;

Route::get('/jobs', [JobController::class, 'index']);
Route::post('/audit-logs', [AuditLogController::class, 'store']);
Route::get('/checklists', [ChecklistController::class, 'index']);
Route::get('/checklist-items', [ChecklistItemController::class, 'index']);
Route::post('/jobs', [JobController::class, 'store']);
Route::post('/photos/upload', [PhotoController::class, 'upload']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
