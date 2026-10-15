<?php

use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Everything that works on company data lives here.
    Route::middleware('company')->group(function () {
        Route::inertia('dashboard', 'dashboard')->name('dashboard');
    });
});

require __DIR__.'/settings.php';
