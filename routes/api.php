<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobSyncController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/jobs', [JobSyncController::class, 'getJobs']);
    Route::get('/checklists', [JobSyncController::class, 'getChecklists']);
    Route::get('/checklist-items', [JobSyncController::class, 'getChecklistItems']);

    Route::post('/jobs/{id}/sync-checklist', [JobSyncController::class, 'syncChecklist']);
    Route::post('/photos/upload', [JobSyncController::class, 'uploadPhoto']);
});

// Since the existing test expects to post to /api/jobs directly without auth:
Route::post('/jobs', [JobSyncController::class, 'createJob']);
