<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\SpeciesController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\RationController;
use \App\Http\Controllers\HerdController;

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
    Route::get('/livestocks/weight', [LivestockController::class, 'weighting'])->name('livestocks.weighting');
    Route::post('/livestocks/weight', [LivestockController::class, 'storeWeight'])->name('livestocks.weight.store');    
    Route::get('/livestocks/milking', [LivestockController::class, 'milking'])->name('livestocks.milking');
    Route::post('/livestocks/milking', [LivestockController::class, 'storeMilking'])->name('livestocks.milking.store');
    
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Herds
    Route::get('/farms.logout', [HomeController::class, 'farmLogout'])->name('farms.logout');
    Route::get('/farms/{farm}/switch', [FarmController::class, 'switch'])->name('farms.switch');
    Route::post('/farms/{farm}/invite', [FarmController::class, 'inviteMember'])->name('farms.invite');
    Route::put('/farms/{farm}/users/{user}/role', [FarmController::class, 'updateRole'])->name('farms.user.role');
    Route::put('/farms/{farm}/users/{user}/profile', [FarmController::class, 'updateUserProfile'])->name('farms.user.profile');
    Route::put('/farms/{farm}/email/{email}/role-invite', [FarmController::class, 'updateRoleInvite'])->name('farms.user.role-invite');
    Route::delete('/farms/{farm}/users/{user}', [FarmController::class, 'destroyMember'])->name('farms.user');
    Route::delete('/farms/{farm}/email/{email}', [FarmController::class, 'destroyMemberInvite'])->name('farms.user.remove-invite'); 
    Route::get('/herds/feeding', [HerdController::class, 'feeding'])->name('herds.feeding');
    Route::post('/herds/feeding', [HerdController::class, 'storeFeeding'])->name('herds.feeding.store');

    // Leftover Feed Routes
    Route::get('/rations/leftover', [RationController::class, 'leftover'])->name('rations.leftover');
    Route::post('/rations/leftover', [RationController::class, 'storeLeftover'])->name('rations.leftover.store');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{notification}/accept', [NotificationController::class, 'accept']);
    Route::post('/notifications/{notification}/reject', [NotificationController::class, 'reject']);
    
    // Farm Invitations
    Route::post('/farm-invites/{invite}/accept', [FarmController::class, 'acceptInvite'])->name('farm-invites.accept');
    Route::post('/farm-invites/{invite}/reject', [FarmController::class, 'rejectInvite'])->name('farm-invites.reject');

    Route::get('/api/species/{species}/breeds', [SpeciesController::class, 'breeds']);
    Route::get('/api/livestocks', [LivestockController::class, 'search'])->name('livestocks.search');
    Route::get('/api/livestocks/rankings', [LivestockController::class, 'rankings'])->name('livestocks.rankings');
    Route::get('/api/herds', [HerdController::class, 'search'])->name('herds.search');
    Route::get('/api/rations', [RationController::class, 'search'])->name('rations.search');

    Route::resources([
        'farms' => FarmController::class,
        'livestocks' => LivestockController::class,
        'herds' => HerdController::class,
        'rations' => RationController::class,
        'feeds' => FeedController::class,
    ]);
    
    // Route::get('/livestocks/photo/{path}', [LivestockController::class, 'showPhoto'])->where('path', '.*')->name('livestocks.photo');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
