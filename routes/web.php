<?php

use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobContactController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\TrackedJobController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Optional post-registration CV onboarding
    Route::get('onboarding/cv', [OnboardingController::class, 'show'])->name('onboarding.cv');
    Route::post('onboarding/cv', [OnboardingController::class, 'store'])
        ->middleware('throttle:uploads')
        ->name('onboarding.cv.store');

    // CVs management
    Route::get('cvs', [CvController::class, 'index'])->name('cvs.index');
    Route::post('cvs', [CvController::class, 'store'])
        ->middleware('throttle:uploads')
        ->name('cvs.store');
    Route::patch('cvs/{cv}/default', [CvController::class, 'setDefault'])->name('cvs.default');
    Route::delete('cvs/{cv}', [CvController::class, 'destroy'])->name('cvs.destroy');

    // Tracked jobs (full CRUD)
    Route::resource('jobs', TrackedJobController::class)->parameters(['jobs' => 'trackedJob']);

    // Job Contacts
    Route::post('jobs/{trackedJob}/contacts', [JobContactController::class, 'store'])->name('jobs.contacts.store');
    Route::put('contacts/{jobContact}', [JobContactController::class, 'update'])->name('contacts.update');
    Route::delete('contacts/{jobContact}', [JobContactController::class, 'destroy'])->name('contacts.destroy');
});

require __DIR__.'/settings.php';
