<?php

namespace App\Services;

use App\Models\Setting;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TelegramService
{
    private ?Nutgram $bot = null;
    
    public function __construct()
    {
        $this->initializeBot();
    }

    private function initializeBot(): void
    {
        $botToken = Setting::getValue('telegram_bot_token');
        
        if ($botToken) {
            try {
                $this->bot = new Nutgram($botToken);
            } catch (\Exception $e) {
                Log::error('Failed to initialize Telegram bot: ' . $e->getMessage());
                $this->bot = null;
            }
        }
    }

    public function isEnabled(): bool
    {
        return Setting::getValue('telegram_notifications_enabled', false) && $this->bot !== null;
    }

    public function getBot(): ?Nutgram
    {
        return $this->bot;
    }

    public function sendMessage(string $chatId, string $message, array $options = []): bool
    {
        if (!$this->isEnabled()) {
            Log::info('Telegram notifications are disabled or bot not configured');
            return false;
        }

        try {
            $this->bot->sendMessage(
                text: $message,
                chat_id: $chatId,
                parse_mode: $options['parse_mode'] ?? 'HTML',
                disable_web_page_preview: $options['disable_web_page_preview'] ?? true
            );

            Log::info('Telegram message sent successfully', [
                'chat_id' => $chatId,
                'message_length' => strlen($message)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendToDefaultChat(string $message, array $options = []): bool
    {
        $defaultChatId = Setting::getValue('telegram_chat_id');
        
        if (!$defaultChatId) {
            Log::warning('No default Telegram chat ID configured');
            return false;
        }

        return $this->sendMessage($defaultChatId, $message, $options);
    }

    public function sendFormattedNotification(array $data): bool
    {
        $message = $this->formatNotificationMessage($data);
        return $this->sendToDefaultChat($message);
    }

    private function formatNotificationMessage(array $data): string
    {
        $appName = Setting::getValue('app_name', 'AI Farm');
        
        $message = "<b>🔔 {$appName} - Notifikasi</b>\n\n";
        
        if (isset($data['title'])) {
            $icon = $this->getNotificationIcon($data['type'] ?? 'info');
            $message .= "<b>{$icon} {$data['title']}</b>\n";
        }
        
        if (isset($data['message'])) {
            $message .= "{$data['message']}\n";
        }
        
        if (isset($data['livestock_name'])) {
            $message .= "\n<b>🐄 Ternak:</b> {$data['livestock_name']}";
        }
        
        if (isset($data['farm_name'])) {
            $message .= "\n<b>🏡 Farm:</b> {$data['farm_name']}";
        }
        
        if (isset($data['created_by'])) {
            $message .= "\n<b>👤 Oleh:</b> {$data['created_by']}";
        }
        
        $message .= "\n\n<i>📅 " . now()->format('d/m/Y H:i') . "</i>";
        
        if (isset($data['action_url'])) {
            $message .= "\n\n<a href=\"{$data['action_url']}\">🔗 Lihat Detail</a>";
        }
        
        return $message;
    }

    private function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            'health' => '🩺',
            'feeding' => '🥛',
            'breeding' => '💕',
            'inventory' => '📦',
            'reminder' => '⏰',
            default => 'ℹ️',
        };
    }

    public function testConnection(): array
    {
        if (!$this->bot) {
            return [
                'success' => false,
                'message' => 'Bot token tidak dikonfigurasi atau tidak valid'
            ];
        }

        try {
            $me = $this->bot->getMe();
            
            return [
                'success' => true,
                'message' => 'Koneksi berhasil!',
                'bot_info' => [
                    'id' => $me->id,
                    'username' => $me->username,
                    'first_name' => $me->first_name,
                    'is_bot' => $me->is_bot
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Telegram: ' . $e->getMessage()
            ];
        }
    }

    public function sendTestMessage(): array
    {
        $defaultChatId = Setting::getValue('telegram_chat_id');
        
        if (!$defaultChatId) {
            return [
                'success' => false,
                'message' => 'Chat ID tidak dikonfigurasi'
            ];
        }

        $appName = Setting::getValue('app_name', 'AI Farm');
        $testMessage = "🧪 <b>Test Notifikasi {$appName}</b>\n\n";
        $testMessage .= "Ini adalah pesan test untuk memastikan bot Telegram bekerja dengan baik.\n\n";
        $testMessage .= "<i>📅 " . now()->format('d/m/Y H:i:s') . "</i>";

        $success = $this->sendMessage($defaultChatId, $testMessage);
        
        return [
            'success' => $success,
            'message' => $success ? 'Pesan test berhasil dikirim!' : 'Gagal mengirim pesan test'
        ];
    }

    public function getChatInfo(string $chatId): array
    {
        if (!$this->bot) {
            return [
                'success' => false,
                'message' => 'Bot tidak dikonfigurasi'
            ];
        }

        try {
            $chat = $this->bot->getChat($chatId);
            
            return [
                'success' => true,
                'chat_info' => [
                    'id' => $chat->id,
                    'type' => $chat->type,
                    'title' => $chat->title ?? null,
                    'username' => $chat->username ?? null,
                    'first_name' => $chat->first_name ?? null,
                    'last_name' => $chat->last_name ?? null,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mendapatkan info chat: ' . $e->getMessage()
            ];
        }
    }

    public function refreshBot(): void
    {
        $this->initializeBot();
        Cache::forget('telegram_bot_status');
    }

    public function getStatus(): array
    {
        return Cache::remember('telegram_bot_status', 300, function () {
            $botToken = Setting::getValue('telegram_bot_token');
            $chatId = Setting::getValue('telegram_chat_id');
            $enabled = Setting::getValue('telegram_notifications_enabled', false);
            
            $status = [
                'configured' => !empty($botToken),
                'chat_configured' => !empty($chatId),
                'enabled' => $enabled,
                'bot_ready' => $this->bot !== null,
                'fully_operational' => $enabled && $this->bot !== null && !empty($chatId)
            ];
            
            if ($status['bot_ready']) {
                $connectionTest = $this->testConnection();
                $status['connection_ok'] = $connectionTest['success'];
                $status['bot_info'] = $connectionTest['bot_info'] ?? null;
            }
            
            return $status;
        });
    }
}