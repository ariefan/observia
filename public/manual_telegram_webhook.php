<?php
// Manual Telegram webhook handler - bypassing Nutgram
require_once 'vendor/autoload.php';

use App\Services\GeminiService;
use App\Models\Setting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

// Get webhook data
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!isset($update['message'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$message = $update['message'];
$chatId = $message['chat']['id'];
$userId = $message['from']['id'];
$text = $message['text'] ?? '';
$chatType = $message['chat']['type'];

// Skip if no text or is a command
if (empty($text) || str_starts_with($text, '/')) {
    echo json_encode(['ok' => true]);
    exit;
}

// Check if AI is enabled
if (!Setting::getValue('telegram_ai_chatbot_enabled', false)) {
    echo json_encode(['ok' => true]);
    exit;
}

// For group chats, only respond if bot is mentioned
$botUsername = 'aifarmv2_bot';
$isPrivate = $chatType === 'private';
$isMentioned = str_contains($text, '@' . $botUsername);

if (!$isPrivate && !$isMentioned) {
    echo json_encode(['ok' => true]);
    exit;
}

// Clean mention from text
$cleanText = str_ireplace('@' . $botUsername, '', $text);
$cleanText = trim($cleanText);

try {
    // Load Laravel app
    require_once 'bootstrap/app.php';
    
    // Get AI response
    $gemini = app(GeminiService::class);
    $response = $gemini->generateResponse($cleanText, (string)$userId);
    
    if ($response) {
        // Send response via Telegram API
        $botToken = Setting::getValue('telegram_bot_token');
        $client = new Client();
        
        $client->post(https://api.telegram.org/bot{$botToken}/sendMessage, [
            'json' => [
                'chat_id' => $chatId,
                'text' => $response,
                'parse_mode' => 'Markdown'
            ]
        ]);
    }
} catch (Exception $e) {
    error_log('Telegram bot error: ' . $e->getMessage());
}

echo json_encode(['ok' => true]);
