<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendJobController;
use App\Http\Controllers\Frontend\JobDetailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\UserSettingsController;
use Illuminate\Support\Facades\Session;
use App\Models\Language;

Route::get('lang/{locale}', function ($locale) {
    // Only allow setting active languages
    $language = Language::where('code', $locale)->where('is_active', true)->first();
    if ($language) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Weiterleitung von der Root-URL zum Frontend-Dashboard (oder Login, falls nicht authentifiziert)
Route::get('/', function () {
    return redirect()->route('frontend.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

// Geschützte Frontend-Routen für Techniker
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('frontend.settings.edit');
    Route::put('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('frontend.settings.password');
    Route::post('/settings/photo', [UserSettingsController::class, 'updateProfilePhoto'])->name('frontend.settings.photo');

    Route::get('/dashboard', [FrontendJobController::class, 'index'])->name('frontend.dashboard');
    Route::get('/job/{job}', [FrontendJobController::class, 'show'])->name('frontend.job.show');
    Route::post('/job/{job}/status', [FrontendJobController::class, 'updateStatus'])->name('frontend.job.status');
    Route::get('/bep-types', [JobDetailController::class, 'getBepTypes'])->name('bep-types.list');
    Route::post('/job/{job}/update-type', [JobDetailController::class, 'updateJobType'])->name('job.update-type');
    Route::post('/checklist/{checklist}/disable', [FrontendJobController::class, 'disableChecklist'])->name('frontend.checklist.disable');
    Route::post('/checklist/{checklist}/submit', [FrontendJobController::class, 'submitChecklist'])->name('frontend.checklist.submit');
    Route::post('/checklist/{checklist}/review', [FrontendJobController::class, 'reviewChecklist'])->name('frontend.checklist.review');
    Route::post('/checklist-item/{item}/save', [FrontendJobController::class, 'saveItem'])->name('frontend.item.save');
    Route::post('/checklist-item/{item}/photo/{photo}/delete', [FrontendJobController::class, 'deleteItemPhoto'])->name('frontend.item.photo.delete');
    Route::post('/checklist-item/{item}/photos/reorder', [FrontendJobController::class, 'reorderItemPhotos'])->name('frontend.item.photo.reorder');
    Route::post('/checklist-item/{item}/toggle', [FrontendJobController::class, 'toggleItem'])->name('frontend.checklist.toggle');
});
