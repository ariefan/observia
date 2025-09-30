<?php

namespace App\Services;

use App\Models\Setting;
use App\Services\GeminiService;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TelegramService
{
    private ?Nutgram $bot = null;
    private GeminiService $geminiService;
    
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        $this->initializeBot();
    }

    private function initializeBot(): void
    {
        // Check if Nutgram class exists
        if (!class_exists('SergiX44\Nutgram\Nutgram')) {
            Log::warning('Nutgram library not found. Please install: composer require nutgram/nutgram');
            $this->bot = null;
            return;
        }

        $botToken = Setting::getValue('telegram_bot_token');
        
        if ($botToken) {
            try {
                $this->bot = new Nutgram($botToken);
                $this->setupBotHandlers();
                // Add fallback handler for all messages        $this->bot->fallback(function ($bot) {            $this->handleTextMessage($bot);        });
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
        $message = "";

        if (isset($data['title'])) {
            $message .= "<b>{$data['title']}</b>\n";
        }

        if (isset($data['message'])) {
            $message .= "{$data['message']}\n";
        }

        if (isset($data['livestock_name'])) {
            $message .= "\n<b>Ternak:</b> {$data['livestock_name']}";
        }

        if (isset($data['farm_name'])) {
            $message .= "\n<b>Farm:</b> {$data['farm_name']}";
        }

        if (isset($data['created_by'])) {
            $message .= "\n<b>Oleh:</b> {$data['created_by']}";
        }

        // Login specific fields
        if (isset($data['user_name'])) {
            $message .= "\n<b>Pengguna:</b> {$data['user_name']}";
        }

        if (isset($data['user_email'])) {
            $message .= "\n<b>Email:</b> {$data['user_email']}";
        }

        if (isset($data['ip_address'])) {
            $message .= "\n<b>IP Address:</b> {$data['ip_address']}";
        }

        if (isset($data['login_method'])) {
            $message .= "\n<b>Metode Login:</b> {$data['login_method']}";
        }

        if (isset($data['user_agent']) && strlen($data['user_agent']) < 100) {
            $message .= "\n<b>Perangkat:</b> " . $this->parseUserAgent($data['user_agent']);
        }

        // Use login_time if provided, otherwise use current time
        $timestamp = isset($data['login_time']) ? $data['login_time'] : now()->format('d/m/Y H:i');
        $message .= "\n\n<i>{$timestamp}</i>";

        if (isset($data['action_url'])) {
            $message .= "\n\n<a href=\"{$data['action_url']}\">Lihat Detail</a>";
        }

        return $message;
    }

    private function parseUserAgent(string $userAgent): string
    {
        // Simple user agent parsing for device info
        if (str_contains($userAgent, 'Mobile')) {
            if (str_contains($userAgent, 'iPhone')) return 'iPhone';
            if (str_contains($userAgent, 'Android')) return 'Android Mobile';
            return 'Mobile Device';
        }
        
        if (str_contains($userAgent, 'iPad')) return 'iPad';
        if (str_contains($userAgent, 'Tablet')) return 'Tablet';
        
        if (str_contains($userAgent, 'Chrome')) return 'Chrome Desktop';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox Desktop';
        if (str_contains($userAgent, 'Safari')) return 'Safari Desktop';
        if (str_contains($userAgent, 'Edge')) return 'Edge Desktop';
        
        return 'Desktop Browser';
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
            'login' => '🔑',
            'logout' => '🚪',
            'security' => '🛡️',
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
            $aiEnabled = Setting::getValue('telegram_ai_chatbot_enabled', false);
            
            $status = [
                'configured' => !empty($botToken),
                'chat_configured' => !empty($chatId),
                'enabled' => $enabled,
                'ai_enabled' => $aiEnabled,
                'bot_ready' => $this->bot !== null,
                'fully_operational' => $enabled && $this->bot !== null && !empty($chatId),
                'ai_operational' => $aiEnabled && $this->geminiService->isEnabled()
            ];
            
            if ($status['bot_ready']) {
                $connectionTest = $this->testConnection();
                $status['connection_ok'] = $connectionTest['success'];
                $status['bot_info'] = $connectionTest['bot_info'] ?? null;
            }
            
            return $status;
        });
    }

    private function setupBotHandlers(): void
    {
        if (!$this->bot) {
            return;
        }

        // Handle /start command
        $this->bot->onCommand('start', function ($bot) {
            $this->handleStartCommand($bot);
        });

        // Handle /help command  
        $this->bot->onCommand('help', function ($bot) {
            $this->handleHelpCommand($bot);
        });

        // Handle all text messages (AI chatbot)
        $this->bot->onText('.*', function ($bot) {
            $this->handleTextMessage($bot);
        });
    }

    private function handleStartCommand($bot): void
    {
        $welcomeMessage = Setting::getValue('telegram_ai_welcome_message', 
            '👋 Halo! Saya adalah asisten AI untuk sistem AI Farm. Silakan tanyakan apa saja tentang peternakan!');
            
        $bot->sendMessage($welcomeMessage, [
            'parse_mode' => 'HTML'
        ]);

        Log::info('Telegram bot start command handled', [
            'user_id' => $bot->userId(),
            'chat_id' => $bot->chatId()
        ]);
    }

    private function handleHelpCommand($bot): void
    {
        $helpMessage = "🤖 <b>AI Farm Assistant Bot</b>\n\n";
        $helpMessage .= "Saya dapat membantu Anda dengan:\n";
        $helpMessage .= "• Pertanyaan tentang peternakan\n";
        $helpMessage .= "• Kesehatan hewan\n";
        $helpMessage .= "• Manajemen farm\n";
        $helpMessage .= "• Breeding dan feeding\n";
        $helpMessage .= "• Dan topik peternakan lainnya\n\n";
        $helpMessage .= "Cukup kirim pesan teks biasa dan saya akan merespons!";

        $bot->sendMessage($helpMessage, [
            'parse_mode' => 'HTML'
        ]);
    }

    private function handleTextMessage($bot): void
    {
        // Skip if AI chatbot is not enabled
        if (!Setting::getValue('telegram_ai_chatbot_enabled', false)) {
            return;
        }

        // Skip commands (they have their own handlers)
        $message = $bot->message()->text;
        if (str_starts_with($message, '/')) {
            return;
        }

        $userId = $bot->userId();
        $chatId = $bot->chatId();
        $isPrivateChat = $bot->chat()->type === 'private';
        $isReplyToBot = $bot->message()->reply_to_message && 
                       $bot->message()->reply_to_message->from->is_bot;
        
        // Check if bot is mentioned in the message
        $botUsername = null;
        try {
            $me = $bot->getMe();
            $botUsername = $me->username;
        } catch (\Exception $e) {
            Log::error('Failed to get bot username: ' . $e->getMessage());
        }
        
        $isMentioned = $botUsername && 
                      (str_contains($message, '@' . $botUsername) || 
                       str_contains(strtolower($message), strtolower($botUsername)));

        // Only respond if:
        // 1. It's a private chat, OR
        // 2. It's a reply to the bot, OR  
        // 3. The bot is mentioned in the message
        if (!$isPrivateChat && !$isReplyToBot && !$isMentioned) {
            return;
        }
        
        // Clean the message (remove bot mention if present)
        $cleanMessage = $message;
        if ($botUsername && $isMentioned) {
            $cleanMessage = str_ireplace('@' . $botUsername, '', $cleanMessage);
            $cleanMessage = str_ireplace($botUsername, '', $cleanMessage);
            $cleanMessage = trim($cleanMessage);
        }

        Log::info('Telegram AI chatbot processing message', [
            'user_id' => $userId,
            'chat_id' => $chatId,
            'is_private' => $isPrivateChat,
            'is_reply' => $isReplyToBot,
            'is_mentioned' => $isMentioned,
            'message_length' => strlen($cleanMessage)
        ]);

        try {
            // Send typing indicator
            $bot->sendChatAction('typing');

            // Get AI response
            $aiResponse = $this->geminiService->generateResponse($cleanMessage, (string)$userId);
            
            if ($aiResponse) {
                $bot->sendMessage($aiResponse, [
                    'parse_mode' => 'Markdown'
                ]);
                
                Log::info('Telegram AI response sent', [
                    'user_id' => $userId,
                    'response_length' => strlen($aiResponse)
                ]);
            } else {
                // Send error message if AI fails
                $errorMessage = Setting::getValue('telegram_ai_error_message',
                    '😔 Maaf, saya sedang mengalami gangguan. Silakan coba lagi dalam beberapa saat.');
                
                $bot->sendMessage($errorMessage);
                
                Log::warning('Telegram AI failed to generate response', [
                    'user_id' => $userId,
                    'message' => $message
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram AI chatbot error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            
            // Send error message
            $errorMessage = Setting::getValue('telegram_ai_error_message',
                '😔 Maaf, saya sedang mengalami gangguan. Silakan coba lagi dalam beberapa saat.');
            
            try {
                $bot->sendMessage($errorMessage);
            } catch (\Exception $sendError) {
                Log::error('Failed to send error message: ' . $sendError->getMessage());
            }
        }
    }

    public function isChatbotEnabled(): bool
    {
        return Setting::getValue('telegram_ai_chatbot_enabled', false) && 
               $this->geminiService->isEnabled() && 
               $this->bot !== null;
    }
}