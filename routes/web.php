<?php

use App\Http\Controllers\CurrentCompanyController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Settings\EquipmentTypeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('onboarding', [OnboardingController::class, 'store'])
        ->middleware('throttle:company-creation')
        ->name('onboarding.store');

    Route::put('current-company', [CurrentCompanyController::class, 'update'])->name('current-company.update');

    // Everything that works on company data lives here.
    Route::middleware('company')->group(function () {
        Route::inertia('dashboard', 'dashboard')->name('dashboard');

        Route::prefix('settings/equipment-types')
            ->name('equipment-types.')
            ->controller(EquipmentTypeController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{equipmentType}/edit', 'edit')->name('edit');
                Route::put('{equipmentType}', 'update')->name('update');
                Route::patch('{equipmentType}/toggle', 'toggle')->name('toggle');
            });
    });
});

require __DIR__.'/settings.php';
