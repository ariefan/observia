<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;

// Telegram webhook (API route - no CSRF protection)
Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook'])->name('api.telegram.webhook');