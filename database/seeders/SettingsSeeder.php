<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'telegram_bot_token',
                'label' => 'Telegram Bot Token',
                'value' => null,
                'type' => 'text',
                'options' => json_encode(['required' => true]),
                'description' => 'Token bot Telegram untuk mengirim notifikasi. Dapatkan dari @BotFather',
                'category' => 'telegram',
                'sort_order' => 1,
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
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
