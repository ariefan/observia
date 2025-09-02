<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureDefaultSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:ensure-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all default settings exist in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultSettings = [
            [
                'key' => 'telegram_bot_token',
                'label' => 'Telegram Bot Token',
                'value' => null,
                'type' => 'text',
                'options' => json_encode(['required' => true]),
                'description' => 'Token bot Telegram untuk mengirim notifikasi. Dapatkan dari @BotFather',
                'category' => 'telegram',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'telegram_chat_id',
                'label' => 'Telegram Chat ID',
                'value' => null,
                'type' => 'text',
                'options' => json_encode(['required' => false]),
                'description' => 'ID chat atau group Telegram untuk notifikasi (opsional)',
                'category' => 'telegram',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'telegram_notifications_enabled',
                'label' => 'Aktifkan Notifikasi Telegram',
                'value' => 'false',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Aktifkan atau nonaktifkan notifikasi via Telegram',
                'category' => 'telegram',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'key' => 'app_name',
                'label' => 'Nama Aplikasi',
                'value' => 'AI Farm',
                'type' => 'text',
                'options' => json_encode(['required' => true]),
                'description' => 'Nama aplikasi yang ditampilkan di sistem',
                'category' => 'general',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'app_version',
                'label' => 'Versi Aplikasi',
                'value' => '1.0.0',
                'type' => 'text',
                'options' => json_encode(['readonly' => true]),
                'description' => 'Versi aplikasi saat ini',
                'category' => 'general',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        $insertedCount = 0;
        $skippedCount = 0;

        foreach ($defaultSettings as $setting) {
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            
            if (!$exists) {
                DB::table('settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $insertedCount++;
                $this->info("✅ Inserted setting: {$setting['key']}");
            } else {
                $skippedCount++;
                $this->line("⏭️  Skipped existing setting: {$setting['key']}");
            }
        }

        $this->newLine();
        $this->info("🎉 Complete! Inserted: {$insertedCount}, Skipped: {$skippedCount}");
        
        if ($insertedCount > 0) {
            $this->info("The new settings are now available in Admin Settings page.");
        }

        return Command::SUCCESS;
    }
}