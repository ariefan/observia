<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TeamController;

Route::get('/', function () {
    // return Inertia::render('Welcome');
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::resources([
        'teams' => TeamController::class,
    ]);

    Route::get('/home', function () {
        return Inertia::render('home/HomePage');
    })->name('home-page');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
