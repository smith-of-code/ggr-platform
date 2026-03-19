<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/{slug}', [CityController::class, 'show'])->name('cities.show');
    Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
    Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/applications/export', [AdminApplicationController::class, 'export'])->name('applications.export');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    Route::resource('cities', AdminCityController::class)->except(['show']);
    Route::patch('/cities/{city}/toggle-active', [AdminCityController::class, 'toggleActive'])->name('cities.toggleActive');
    Route::resource('tours', AdminTourController::class)->except(['show']);
    Route::patch('/tours/{tour}/toggle-active', [AdminTourController::class, 'toggleActive'])->name('tours.toggleActive');

    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/mail', [AdminSettingsController::class, 'mail'])->name('settings.mail');
    Route::put('/settings/mail', [AdminSettingsController::class, 'updateMail'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [AdminSettingsController::class, 'testMail'])->name('settings.mail.test');
});

require __DIR__.'/auth.php';
