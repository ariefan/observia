<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;

class DirectTelegramController extends Controller
{
    private GeminiService $geminiService;
    private Client $httpClient;
    private ?string $botToken = null;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        $this->httpClient = new Client(['timeout' => 30]);
        $this->botToken = Setting::getValue('telegram_bot_token');
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            Log::info('DirectTelegramController webhook received');

            if (!$this->botToken) {
                Log::error('Telegram bot token not configured');
                return response()->json(['ok' => false, 'error' => 'Bot not configured']);
            }

            $update = $request->json()->all();
            
            if (empty($update) || !isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $userId = $message['from']['id'] ?? null;
            $text = $message['text'] ?? '';

            if (!$chatId || !$userId || empty($text)) {
                return response()->json(['ok' => true]);
            }

            if (!Setting::getValue('telegram_ai_chatbot_enabled', false)) {
                return response()->json(['ok' => true]);
            }

            Log::info('Processing Telegram message', [
                'user_id' => $userId,
                'chat_id' => $chatId,
                'message' => substr($text, 0, 50) . (strlen($text) > 50 ? '...' : '')
            ]);

            if (str_starts_with($text, '/')) {
                $this->handleCommand($chatId, $text);
                return response()->json(['ok' => true]);
            }

            $this->handleChatMessage($chatId, $text, $userId);
            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('DirectTelegramController webhook error: ' . $e->getMessage());
            return response()->json(['ok' => true]);
        }
    }

    private function handleCommand(int|string $chatId, string $command): void
    {
        switch (strtolower(trim($command))) {
            case '/start':
                $message = '👋 Halo! Saya adalah asisten AI untuk sistem AI Farm. Silakan tanyakan apa saja tentang peternakan!';
                $this->sendMessage($chatId, $message);
                break;
            case '/help':
                $message = "🤖 AI Farm Assistant Bot\n\nSaya dapat membantu dengan pertanyaan peternakan, kesehatan hewan, dan manajemen farm.";
                $this->sendMessage($chatId, $message);
                break;
        }
    }

    private function handleChatMessage(int|string $chatId, string $text, int|string $userId): void
    {
        try {
            $this->sendTypingAction($chatId);
            $response = $this->geminiService->generateResponse($text, (string)$userId);

            if ($response) {
                $this->sendMessage($chatId, $response);
                Log::info('AI response sent', ['user_id' => $userId, 'response_length' => strlen($response)]);
            } else {
                $this->sendMessage($chatId, 'Maaf, saya sedang mengalami gangguan. Silakan coba lagi nanti.');
            }

        } catch (\Exception $e) {
            Log::error('Error handling chat message: ' . $e->getMessage(), ['user_id' => $userId]);
            $this->sendMessage($chatId, 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    private function sendTypingAction(int|string $chatId): void
    {
        try {
            $this->httpClient->post("https://api.telegram.org/bot{$this->botToken}/sendChatAction", [
                'form_params' => ['chat_id' => $chatId, 'action' => 'typing']
            ]);
        } catch (\Exception $e) {
            Log::debug('Failed to send typing action');
        }
    }

    private function sendMessage(int|string $chatId, string $text): bool
    {
        try {
            $response = $this->httpClient->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'form_params' => [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if ($data['ok']) {
                Log::info('Telegram message sent successfully', ['chat_id' => $chatId]);
                return true;
            } else {
                Log::error('Telegram API error: ' . ($data['description'] ?? 'Unknown'));
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
            return false;
        }
    }
}
