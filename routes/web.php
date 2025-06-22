<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    // return Inertia::render('Welcome');
    return redirect()->route('login');
})->name('welcome');

Route::get('/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resources([
        'farms' => FarmController::class,
        'livestocks' => LivestockController::class,
    ]);
    
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::put('/farms/{farm}/switch', [FarmController::class, 'switch'])->name('farm.switch');
    Route::put('/farms/{farm}/users/{user}/role', [FarmController::class, 'updateRole'])->name('farm.user.role');

    // Route::get('/dashboard', function () {
    //     return Inertia::render('Dashboard');
    // })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
