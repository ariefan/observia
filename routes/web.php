<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\SpeciesController;

Route::get('/', function () {
    // return Inertia::render('Welcome');
    return redirect()->route('login');
})->name('welcome');

Route::get('/clear-all', function ($request) {
    \Artisan::call('optimize:clear');
    \Artisan::call('config:cache');
    \Artisan::call('route:cache');
    \Artisan::call('view:clear');
    \Illuminate\Support\Facades\Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return 'All caches cleared!';
})->name('clear.all');

Route::get('/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resources([
        'farms' => FarmController::class,
        'livestocks' => LivestockController::class,
    ]);
    
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Farms
    Route::get('/farms.logout', [HomeController::class, 'farmLogout'])->name('farms.logout');
    Route::get('/farms/{farm}/switch', [FarmController::class, 'switch'])->name('farms.switch');
    Route::post('/farms/{farm}/invite', [FarmController::class, 'inviteMember'])->name('farms.invite');
    Route::put('/farms/{farm}/users/{user}/role', [FarmController::class, 'updateRole'])->name('farms.user.role');
    Route::put('/farms/{farm}/email/{email}/role-invite', [FarmController::class, 'updateRoleInvite'])->name('farms.user.role-invite');
    Route::delete('/farms/{farm}/users/{user}', [FarmController::class, 'destroyMember'])->name('farms.user');
    Route::delete('/farms/{farm}/email/{email}', [FarmController::class, 'destroyMemberInvite'])->name('farms.user.remove-invite');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{notification}/accept', [NotificationController::class, 'accept']);
    Route::post('/notifications/{notification}/reject', [NotificationController::class, 'reject']);

    Route::get('/api/species/{species}/breeds', [SpeciesController::class, 'breeds']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
