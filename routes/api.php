<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectTelegramController;

// Telegram webhook (API route - no CSRF protection)
Route::post('/telegram/webhook', [DirectTelegramController::class, 'webhook'])->name('api.telegram.webhook');
