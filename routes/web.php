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
use App\Http\Controllers\SuperDashboardController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\ProduktivitasController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContentController;

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
    
    // Notification Route
    Route::get('/notification', function () {
        return Inertia::render('NotificationTest');
    })->name('notification.page');
    
    // Reports Route
    Route::get('/laporan', function () {
        return Inertia::render('Laporan');
    })->name('reports.index');
    
    // Superuser routes
    Route::get('/super-dashboard', [SuperDashboardController::class, 'index'])->name('super.dashboard');
    Route::post('/super-dashboard/create-superuser', [SuperDashboardController::class, 'createSuperUser'])->name('super.create-user');
    Route::delete('/super-dashboard/remove-superuser/{user}', [SuperDashboardController::class, 'removeSuperUser'])->name('super.remove-user');
    
    // Super admin only routes for Species and Breeds management
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::resource('species', \App\Http\Controllers\SpeciesController::class);
        Route::resource('breeds', \App\Http\Controllers\BreedController::class);
    });

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
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
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

    // Audit Trail Routes
    Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
    Route::get('/audits/export', [AuditController::class, 'export'])->name('audits.export');
    Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
    Route::get('/api/audits/{modelType}/{modelId}', [AuditController::class, 'model'])->name('audits.model');

    // Error Log Routes (Super User Only)
    Route::get('/error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
    Route::get('/error-logs/export', [ErrorLogController::class, 'export'])->name('error-logs.export');
    Route::get('/error-logs/{errorLog}', [ErrorLogController::class, 'show'])->name('error-logs.show');

    // Login Log Routes
    Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');
    Route::get('/login-logs/export', [LoginLogController::class, 'export'])->name('login-logs.export');
    Route::get('/login-logs/{loginLog}', [LoginLogController::class, 'show'])->name('login-logs.show');

    // Productivity Routes
    Route::get('/productivity', [ProduktivitasController::class, 'susu'])->name('productivity.index');
    Route::get('/productivity/milk', [ProduktivitasController::class, 'susu'])->name('productivity.milk');
    Route::get('/productivity/weight', [ProduktivitasController::class, 'bobot'])->name('productivity.weight');

    // Report Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.api');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // Content Management Routes (Super User Only)
    Route::get('/content-management', [ContentController::class, 'index'])->name('content.management');
    Route::get('/api/content', [ContentController::class, 'getContent'])->name('content.api');
    Route::post('/api/videos', [ContentController::class, 'storeVideo'])->name('videos.store');
    Route::put('/api/videos/{video}', [ContentController::class, 'updateVideo'])->name('videos.update');
    Route::delete('/api/videos/{video}', [ContentController::class, 'destroyVideo'])->name('videos.destroy');
    Route::post('/api/articles', [ContentController::class, 'storeArticle'])->name('articles.store');
    Route::put('/api/articles/{article}', [ContentController::class, 'updateArticle'])->name('articles.update');
    Route::delete('/api/articles/{article}', [ContentController::class, 'destroyArticle'])->name('articles.destroy');

    Route::resources([
        'farms' => FarmController::class,
        'livestocks' => LivestockController::class,
        'livestock-endings' => \App\Http\Controllers\LivestockEndingController::class,
        'herds' => HerdController::class,
        'rations' => RationController::class,
        'feeds' => FeedController::class,
    ]);
    
    // Route::get('/livestocks/photo/{path}', [LivestockController::class, 'showPhoto'])->where('path', '.*')->name('livestocks.photo');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
