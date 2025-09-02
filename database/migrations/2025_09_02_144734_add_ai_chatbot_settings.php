<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add AI Chatbot settings to existing settings table
        $aiChatbotSettings = [
            [
                'key' => 'telegram_ai_chatbot_enabled',
                'label' => 'Aktifkan AI Chatbot Telegram',
                'value' => 'false',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Aktifkan chatbot AI yang dapat merespons pesan di Telegram menggunakan Gemini AI',
                'category' => 'telegram',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_ai_bot_instruction',
                'label' => 'Instruksi Bot AI',
                'value' => 'Anda adalah asisten AI untuk sistem manajemen farm. Bantu pengguna dengan pertanyaan tentang peternakan, kesehatan hewan, breeding, feeding, dan manajemen farm. Gunakan bahasa Indonesia yang ramah dan profesional.',
                'type' => 'textarea',
                'options' => json_encode(['rows' => 5, 'placeholder' => 'Masukkan instruksi untuk AI chatbot...']),
                'description' => 'Instruksi sistem untuk AI chatbot yang menentukan bagaimana bot akan berperilaku dan merespons',
                'category' => 'telegram',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_ai_welcome_message',
                'label' => 'Pesan Selamat Datang AI Bot',
                'value' => '👋 Halo! Saya adalah asisten AI untuk sistem AI Farm. Saya siap membantu Anda dengan pertanyaan tentang peternakan, kesehatan hewan, manajemen farm, dan lainnya. Silakan tanyakan apa saja!',
                'type' => 'textarea',
                'options' => json_encode(['rows' => 3, 'placeholder' => 'Pesan selamat datang untuk pengguna baru...']),
                'description' => 'Pesan yang akan dikirim ketika pengguna pertama kali berinteraksi dengan AI chatbot',
                'category' => 'telegram',
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_ai_error_message',
                'label' => 'Pesan Error AI Bot',
                'value' => '😔 Maaf, saya sedang mengalami gangguan. Silakan coba lagi dalam beberapa saat atau hubungi administrator sistem.',
                'type' => 'textarea',
                'options' => json_encode(['rows' => 2, 'placeholder' => 'Pesan ketika AI tidak dapat merespons...']),
                'description' => 'Pesan yang akan dikirim ketika AI chatbot mengalami error atau tidak dapat merespons',
                'category' => 'telegram',
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('settings')->insert($aiChatbotSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove AI Chatbot settings
        DB::table('settings')->whereIn('key', [
            'telegram_ai_chatbot_enabled',
            'telegram_ai_bot_instruction',
            'telegram_ai_welcome_message',
            'telegram_ai_error_message'
        ])->delete();
    }
};
