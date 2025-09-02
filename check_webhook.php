<?php

// Simple webhook checker without Laravel
$botToken = trim(file_get_contents('.env') ? preg_match('/TELEGRAM_BOT_TOKEN=([^\n]+)/', file_get_contents('.env'), $matches) ? $matches[1] : '' : '');

if (!$botToken || $botToken === 'your_bot_token_here') {
    echo "Bot token not found in .env file!\n";
    exit(1);
}

$botToken = trim($botToken, '"');

$webhookUrl = 'https://app.aifarm.id/api/telegram/webhook';

// Check current webhook info
echo "Checking current webhook for bot token: " . substr($botToken, 0, 10) . "...\n";
$getWebhookUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $getWebhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$webhookInfo = json_decode($response, true);

if ($webhookInfo && $webhookInfo['ok']) {
    echo "Current webhook URL: " . ($webhookInfo['result']['url'] ?: 'Not set') . "\n";
    echo "Pending updates: " . ($webhookInfo['result']['pending_update_count'] ?? 0) . "\n";
    echo "Last error: " . ($webhookInfo['result']['last_error_message'] ?? 'None') . "\n";
} else {
    echo "Error getting webhook info: $response\n";
}

// Set webhook
echo "\nSetting webhook to: $webhookUrl\n";
$setWebhookUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

$postData = [
    'url' => $webhookUrl,
    'drop_pending_updates' => true // Clear any pending updates
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $setWebhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && $result['ok']) {
    echo "✅ Webhook set successfully!\n";
    echo "Description: " . ($result['description'] ?? '') . "\n";
} else {
    echo "❌ Failed to set webhook\n";
    echo "Response: $response\n";
}
