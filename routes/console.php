<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule backup based on admin settings
Schedule::command('backup:create')
    ->dailyAt(Setting::getValue('backup_time', '01:00'))
    ->when(function () {
        return filter_var(Setting::getValue('backup_enabled', false), FILTER_VALIDATE_BOOLEAN);
    })
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Scheduled backup completed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled backup failed');
    });
